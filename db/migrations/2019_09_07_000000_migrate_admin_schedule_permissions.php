<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class MigrateAdminSchedulePermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }

        $this->schema->getConnection()
            ->table('Privileges')
            ->where('name', 'admin_import')
            ->update(
                [
                    'name' => 'schedule.import',
                    'desc' => 'Import rooms and shifts from schedule.xml',
                ]
            );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }

        $this->schema->getConnection()
            ->table('Privileges')
            ->where('name', 'schedule.import')
            ->update(
                [
                    'name' => 'admin_import',
                    'desc' => 'Import rooms and shifts from schedule.xcs/schedule.xcal',
                ]
            );
    }
}
