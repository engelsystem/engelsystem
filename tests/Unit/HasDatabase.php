<?php

namespace Engelsystem\Test\Unit;

use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Http\Request;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use PDO;
use Psr\Http\Message\ServerRequestInterface;

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

        $this->app->instance(Database::class, $this->database);
        $this->app->register(MigrationServiceProvider::class);

        $this->app->instance(ServerRequestInterface::class, new Request());

        /** @var Migrate $migration */
        $migration = $this->app->get('db.migration');
        $migration->initMigration();

        $this->database
            ->getConnection()
            ->table('migrations')
            ->insert(
                [
                    // Migrations that can be skipped as they only use legacy tables
                    ['migration' => '2018_01_01_000001_import_install_sql'],
                    ['migration' => '2018_01_01_000002_import_update_sql'],
                    ['migration' => '2018_01_01_000003_fix_old_tables'],
                    ['migration' => '2018_01_01_000004_cleanup_group_privileges'],
                    ['migration' => '2018_01_01_000005_add_angel_supporter_permissions'],
                    ['migration' => '2018_12_27_000000_fix_missing_arrival_dates'],
                    ['migration' => '2019_09_07_000000_migrate_admin_schedule_permissions'],
                    ['migration' => '2020_04_07_000000_change_mysql_database_encoding_to_utf8mb4'],
                    ['migration' => '2020_09_12_000000_create_welcome_angel_permissions_group'],
                    ['migration' => '2020_12_28_000000_oauth_set_identifier_binary'],
                    ['migration' => '2021_08_26_000000_add_shirt_edit_permissions'],
                    ['migration' => '2021_10_12_000000_add_shifts_description'],
                    ['migration' => '2021_12_30_000000_remove_admin_news_html_privilege'],
                    ['migration' => '2022_06_02_000000_create_voucher_edit_permission'],
                    ['migration' => '2022_06_03_000000_shifts_add_transaction_id'],
                ]
            );

        $migration->run(__DIR__ . '/../../db/migrations');
    }
}
