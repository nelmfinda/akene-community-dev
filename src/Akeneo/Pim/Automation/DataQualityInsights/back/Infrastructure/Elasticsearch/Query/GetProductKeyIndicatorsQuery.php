<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductKeyIndicatorsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductKeyIndicatorsQuery implements GetProductKeyIndicatorsQueryInterface
{
    public function __construct(
        private Client $esClient,
        private CategoryRepositoryInterface $categoryRepository,
        private string $documentType
    ) {
    }

    public function all(ChannelCode $channelCode, LocaleCode $localeCode, KeyIndicatorCode ...$keyIndicatorCodes): array
    {
        return $this->executeQuery($channelCode, $localeCode, $keyIndicatorCodes);
    }

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family, KeyIndicatorCode ...$keyIndicatorCodes): array
    {
        $terms = [['term' => ['family.code' => (string)$family]]];

        return $this->executeQuery($channelCode, $localeCode, $keyIndicatorCodes, $terms);
    }

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category, KeyIndicatorCode ...$keyIndicatorCodes): array
    {
        $categoryCode = (string)$category;
        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);
        $categoryChildren = $category instanceof CategoryInterface ? $this->categoryRepository->getAllChildrenCodes($category) : [];

        $terms = [['terms' => ['categories' => array_merge([$categoryCode], $categoryChildren)]]];

        return $this->executeQuery($channelCode, $localeCode, $keyIndicatorCodes, $terms);
    }

    private function executeQuery(ChannelCode $channelCode, LocaleCode $localeCode, array $keyIndicatorCodes, array $extraTerms = []): array
    {
        if (empty($keyIndicatorCodes)) {
            return [];
        }

        $query = [
            'bool' => [
                'must' => array_merge([[
                    'term' => [
                        'document_type' => $this->documentType
                    ],
                ]], $extraTerms),
            ],
        ];

        if ($this->documentType === ProductModelInterface::class) {
            // here we do not want to consider product_model which are child of another product model (aka "submodels")
            // this is to be consistent with the product grid in front
            $query['bool']['must_not'] = ["exists" => ['field' => 'parent']];
        }

        $aggregations = [];
        foreach ($keyIndicatorCodes as $keyIndicatorCode) {
            $aggregations[(string)$keyIndicatorCode]['terms']['field'] = sprintf(
                'data_quality_insights.key_indicators.%s.%s.%s',
                $channelCode,
                $localeCode,
                $keyIndicatorCode
            );
        }

        $searchQuery = [
            'size' => 0,
            'query' => $query,
            'aggs' => $aggregations,
        ];

        $result = $this->esClient->search($searchQuery);

        $keyIndicators = [];
        foreach ($result['aggregations'] ?? [] as $keyIndicatorCode => $aggregationResult) {
            $aggregationBuckets = $aggregationResult['buckets'] ?? [];
            if (empty($aggregationBuckets)) {
                continue;
            }

            Assert::isArray($aggregationBuckets);
            $keyIndicators[$keyIndicatorCode] = $this->formatKeyIndicator(strval($keyIndicatorCode), $aggregationBuckets);
        }

        return $keyIndicators;
    }

    private function formatKeyIndicator(string $keyIndicatorCode, array $aggregationBuckets): KeyIndicator
    {
        $totalGood = 0;
        $totalToImprove = 0;

        foreach ($aggregationBuckets as $bucket) {
            $keyIndicatorValue = $bucket['key'] ?? null;

            if (1 === $keyIndicatorValue) {
                $totalGood = (int)($bucket['doc_count'] ?? 0);
            } elseif (0 === $keyIndicatorValue) {
                $totalToImprove = (int)($bucket['doc_count'] ?? 0);
            }
        }

        return new KeyIndicator(new KeyIndicatorCode($keyIndicatorCode), $totalGood, $totalToImprove);
    }
}
