<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Helper;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Test\Integration\TestCase;

class CategoryTestCase extends TestCase
{
    /**
     * @param array<string, string>|null $labels
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createOrUpdateCategory(
        string $code,
        ?int $id = null,
        ?array $labels = [],
        ?int $parentId = null,
    ): Category {
        $categoryId = (null === $id ? null : new CategoryId($id));
        $parentId = (null === $parentId ? null : new CategoryId($parentId));

        $categoryModelToCreate = new Category(
            id: $categoryId,
            code: new Code($code),
            labelCollection: LabelCollection::fromArray($labels),
            parentId: $parentId,
        );

        // Insert the category in pim_catalog_category
        $this->get(UpsertCategoryBase::class)->execute($categoryModelToCreate);

        // Get the data of the newly inserted category from pim_catalog_category
        $categoryBase = $this->get(GetCategoryInterface::class)->byCode((string) $categoryModelToCreate->getCode());
        $parentId =
            $categoryBase->getParentId() === null
                ? null
                : new CategoryId($categoryBase->getParentId()->getValue());

        $categoryModelWithId = new Category(
            new CategoryId($categoryBase->getId()->getValue()),
            new Code((string) $categoryBase->getCode()),
            $categoryModelToCreate->getLabelCollection(),
            $parentId,
        );
        $this->get(UpsertCategoryTranslations::class)->execute($categoryModelWithId);

        $categoryTranslations = $this->get(GetCategoryInterface::class)->byCode((string) $categoryModelToCreate->getCode())->getLabelCollection()->getLabels();

        $createdParentId =
            $categoryBase->getParentId()?->getValue() > 0
            ? new CategoryId($categoryBase->getParentId()->getValue())
            : null;

        // Instantiates a new Category model based on data fetched in database
        return new Category(
            new CategoryId($categoryBase->getId()->getValue()),
            new Code((string) $categoryBase->getCode()),
            LabelCollection::fromArray($categoryTranslations),
            $createdParentId,
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
