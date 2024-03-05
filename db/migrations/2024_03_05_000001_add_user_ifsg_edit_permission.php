<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddUserIfsgEditPermission extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                'name' => 'user.ifsg.edit', 'description' => 'Edit IfSG Certificate',
            ]);

        $editIfsg = $db->table('privileges')
            ->where('name', 'user.ifsg.edit')
            ->get(['id'])
            ->first();

        $shico = 60;
        $team_coordinator = 65;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $shico, 'privilege_id' => $editIfsg->id],
                ['group_id' => $team_coordinator, 'privilege_id' => $editIfsg->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'user.ifsg.edit')
            ->delete();
    }
}
