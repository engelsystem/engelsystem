<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddLogsAllPermission extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                ['name' => 'logs.all', 'description' => 'View all logs'],
            ]);

        $logsAll = $db->table('privileges')
            ->where('name', 'logs.all')
            ->get(['id'])
            ->first();

        $bureaucrat = 80;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $bureaucrat, 'privilege_id' => $logsAll->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'logs.all')
            ->delete();
    }
}
