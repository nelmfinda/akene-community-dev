<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\ValueObject;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductUuid
{
    private function __construct(private UuidInterface $uuid)
    {
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }
}
