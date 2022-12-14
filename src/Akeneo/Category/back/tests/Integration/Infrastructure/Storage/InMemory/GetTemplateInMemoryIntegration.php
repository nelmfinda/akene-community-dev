<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemoryIntegration extends TestCase
{
    public function testGetTemplateById(): void
    {
        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedTemplate = $this->givenTemplate($templateUuid);
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        $this->assertEquals($expectedTemplate, $template);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenTemplate(string $templateUuid): Template
    {
        $templateUuid = TemplateUuid::fromString($templateUuid);

        return new Template(
            $templateUuid,
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeRichText::create(
                    AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                    new AttributeCode('description'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Description']),
                    $templateUuid
                ),
                AttributeImage::create(
                    AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['fr_FR' => 'Banner image']),
                    $templateUuid
                ),
                AttributeText::create(
                    AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
                    new AttributeCode('seo_meta_title'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Title']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                    new AttributeCode('seo_meta_description'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9'),
                    new AttributeCode('seo_keywords'),
                    AttributeOrder::fromInteger(5),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Keywords']),
                    $templateUuid
                ),
            ])
        );
    }
}
