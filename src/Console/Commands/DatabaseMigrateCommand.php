<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Database\Migration\Direction;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'db:migrate',
    description: 'Run database migrations'
)]
class DatabaseMigrateCommand extends Command
{
    public function __construct(
        protected ContainerInterface $container
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('down', 'd', InputOption::VALUE_NONE, 'Rollback migrations')
            ->addOption('one-step', null, InputOption::VALUE_NONE, 'Only run one migration')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force migration even if locked')
            ->addOption('prune', 'p', InputOption::VALUE_NONE, 'Prune all database tables before run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Engelsystem\Application $app */
        $app = $this->container;
        $app->register(MigrationServiceProvider::class);

        /** @var Migrate $migration */
        $migration = $app->get('db.migration');
        $migration->setOutput(fn ($text) => $this->io->writeln($text));

        $baseDir = $app->path() . '/db/migrations';
        $direction = $input->getOption('down') ? Direction::DOWN : Direction::UP;
        $oneStep = $input->getOption('one-step');
        $force = $input->getOption('force');
        $prune = $input->getOption('prune');

        $migration->run($baseDir, $direction, $oneStep, $force, $prune);

        $this->success('Migrations completed');

        return self::SUCCESS;
    }
}
