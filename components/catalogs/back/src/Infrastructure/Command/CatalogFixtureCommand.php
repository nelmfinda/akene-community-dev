<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Command;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppRemover;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogFixtureCommand extends Command
{
    protected static $defaultName = 'akeneo:catalogs:fixtures';
    protected static $defaultDescription = 'Do not run this command in production env. Installs fixtures for dev only.';

    public function __construct(
        private ConnectedAppFactory $connectedAppFactory,
        private CommandBus $commandBus,
        private UserRepositoryInterface $userRepository,
        private ConnectedAppRemover $connectedAppRemover,
        private Connection $connection,
        private string $env,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a catalog and the associated connected App.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('dev' !== $this->env) {
            $output->writeln('This command must be run in dev environment.');

            return self::INVALID;
        }

        $this->connection->beginTransaction();

        try {
            try {
                $this->connectedAppRemover->remove('555d7447-2dab-474e-9026-f5d33c401b74');
            } catch (\Throwable $exception) {
            }

            $connectedApp = $this->connectedAppFactory->createFakeConnectedAppWithValidToken(
                '555d7447-2dab-474e-9026-f5d33c401b74',
                'shopifi',
                [
                    'read_catalogs',
                    'write_catalogs',
                    'delete_catalogs',
                    'read_products',
                ]
            );

            /** @var UserInterface|null $user */
            $user = $this->userRepository->findOneBy(['username' => $connectedApp->getUsername()]);
            \assert(null !== $user);

            $this->commandBus->execute(new CreateCatalogCommand(
                Uuid::uuid4()->toString(),
                'Store US',
                $user->getUserIdentifier(),
            ));

            $this->commandBus->execute(new CreateCatalogCommand(
                Uuid::uuid4()->toString(),
                'Store FR',
                $user->getUserIdentifier(),
            ));

            $this->connection->commit();

            return self::SUCCESS;
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            $output->writeln($exception->getMessage());

            return self::FAILURE;
        }
    }
}
