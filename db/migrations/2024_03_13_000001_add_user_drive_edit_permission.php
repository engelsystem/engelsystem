<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddUserDriveEditPermission extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                'name' => 'user.drive.edit', 'description' => 'Edit Driving License',
            ]);

        $editDrive = $db->table('privileges')
            ->where('name', 'user.drive.edit')
            ->get(['id'])
            ->first();

        $shico = 60;
        $team_coordinator = 65;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $shico, 'privilege_id' => $editDrive->id],
                ['group_id' => $team_coordinator, 'privilege_id' => $editDrive->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'user.drive.edit')
            ->delete();
    }
}
