<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetLocalesQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLocalesQuery implements GetLocalesQueryInterface
{
    public function __construct(private LocaleRepositoryInterface $localeRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        $locales = $this->localeRepository->getActivatedLocales();

        return \array_map(static fn (LocaleInterface $locale): array => [
            'code' => $locale->getCode(),
            'label' => $locale->getName() ?? \sprintf('[%s]', $locale->getCode()),
        ], $locales);
    }
}
