<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddUserInfoPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                ['name' => 'user.info.show', 'description' => 'Show User Info'],
                ['name' => 'user.info.edit', 'description' => 'Edit User Info'],
            ]);

        $showUserInfo = $db->table('privileges')
            ->where('name', 'user.info.show')
            ->get(['id'])
            ->first();

        $editUserInfo = $db->table('privileges')
            ->where('name', 'user.info.edit')
            ->get(['id'])
            ->first();

        $buerocrat = 80;
        $shico = 60;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $buerocrat, 'privilege_id' => $editUserInfo->id],
                ['group_id' => $shico, 'privilege_id' => $showUserInfo->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->whereIn('name', ['user.info.edit', 'user.info.show'])
            ->delete();
    }
}
