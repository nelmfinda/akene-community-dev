<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeImage extends Attribute
{
    protected function __construct(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeType $type,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateUuid $templateId,
    ) {
        parent::__construct(
            $uuid,
            $code,
            $type,
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId,
        );
    }

    public static function create(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateUuid $templateId,
    ): AttributeImage {
        return new self(
            $uuid,
            $code,
            new AttributeType(AttributeType::IMAGE),
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId,
        );
    }

    /**
     * @return array{
     *     identifier: string,
     *     code: string,
     *     type: string,
     *     order: int,
     *     is_localizable: bool,
     *     labels: array<string, string>,
     *     template_identifier: string
     * }
     */
    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [],
        );
    }
}
