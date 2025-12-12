<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Http\Request;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection;
use PDO;
use Psr\Http\Message\ServerRequestInterface;

trait HasDatabase
{
    protected Database $database;

    /**
     * Setup in memory database, cache migrated state between tests
     */
    protected function initDatabase(): void
    {
        $dbManager = new CapsuleManager();
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]);
        $dbManager->bootEloquent();

        $connection = $dbManager->getConnection();
        $connection->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->database = new Database($connection);

        $this->app->instance(Database::class, $this->database);
        $this->app->instance(Connection::class, $connection);
        $this->app->register(MigrationServiceProvider::class);

        $this->app->instance(ServerRequestInterface::class, new Request());

        $this->restoreDatabase($connection);

        if (!$connection->getSchemaBuilder()->hasTable('migrations')) {
            $this->runMigration();

            $this->storeDatabase($connection);
        }
    }

    protected function runMigration(): void
    {
        /** @var Migrate $migration */
        $migration = $this->app->get('db.migration');
        $migration->initMigration();

        $this->database
            ->getConnection()
            ->table('migrations')
            ->insert(
                [
                    // Migrations that can be skipped as they only use legacy tables
                    // or only change data not available/relevant in test migrations
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
                    ['migration' => '2021_05_23_000000_create_first_user'],
                    ['migration' => '2021_05_23_000000_set_admin_password'],
                    ['migration' => '2021_08_26_000000_add_shirt_edit_permissions'],
                    ['migration' => '2021_10_12_000000_add_shifts_description'],
                    ['migration' => '2021_12_30_000000_remove_admin_news_html_privilege'],
                    ['migration' => '2022_06_02_000000_create_voucher_edit_permission'],
                    ['migration' => '2022_06_03_000000_shifts_add_transaction_id'],
                    ['migration' => '2022_07_21_000000_fix_old_groups_table_id_and_name'],
                    ['migration' => '2022_10_21_000000_add_hide_register_to_angeltypes'],
                    ['migration' => '2022_11_06_000000_shifttype_remove_angeltype'],
                    ['migration' => '2023_05_21_000001_cleanup_short_api_keys'],
                    ['migration' => '2025_12_12_000000_change_oauth_identifier_database_encoding_to_bin'],
                ]
            );

        $migration->run(__DIR__ . '/../../db/migrations');
    }

    protected function storeDatabase(Connection $connection): void
    {
        $schema = $connection->getSchemaBuilder();
        $dbState = [];
        foreach ($schema->getTables() as $table) {
            // Get table structure
            $name = $table['name'];
            $sql = $connection
                ->table('sqlite_master')
                ->where('name', $name)
                ->first()
                ->sql;

            // Save database content
            $rows = [];
            $data = $connection
                ->table($name)
                ->get();
            foreach ($data as $row) {
                $rows[] = (array) $row;
            }

            $dbState[$name] = [
                'name' => $name,
                'info' => $table,
                'sql' => $sql,
                'rows' => $rows,
            ];
        }

        RuntimeTest::$dbState = $dbState;
    }

    protected function restoreDatabase(Connection $connection): void
    {
        // Create tables
        foreach (RuntimeTest::$dbState as $table) {
            $connection->statement($table['sql']);
        }

        // Restore data
        $schema = $connection->getSchemaBuilder();
        $schema->disableForeignKeyConstraints();
        foreach (RuntimeTest::$dbState as $table) {
            $connection
                ->table($table['name'])
                ->insert($table['rows']);
        }
        $schema->enableForeignKeyConstraints();
    }
}
