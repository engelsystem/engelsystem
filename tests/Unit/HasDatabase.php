<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Application;
use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use PDO;

trait HasDatabase
{
    /** @var Database */
    protected $database;

    /**
     * Setup in memory database
     */
    protected function initDatabase()
    {
        $dbManager = new CapsuleManager();
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $dbManager->bootEloquent();

        $connection = $dbManager->getConnection();
        $connection->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->database = new Database($connection);

        $app = new Application();
        $app->instance(Database::class, $this->database);
        $app->register(MigrationServiceProvider::class);

        /** @var Migrate $migration */
        $migration = $app->get('db.migration');
        $migration->initMigration();

        $this->database
            ->getConnection()
            ->table('migrations')
            ->insert([
                ['migration' => '2018_01_01_000001_import_install_sql'],
                ['migration' => '2018_01_01_000002_import_update_sql'],
                ['migration' => '2018_01_01_000003_fix_old_tables'],
                ['migration' => '2018_01_01_000004_cleanup_group_privileges'],
            ]);

        $migration->run(__DIR__ . '/../../db/migrations');
    }
}
