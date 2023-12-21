<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddUserEditPermission extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                'name' => 'user.edit', 'description' => 'Edit user',
            ]);

        $editUser = $db->table('privileges')
            ->where('name', 'user.edit')
            ->get(['id'])
            ->first();

        $buerocrat = 80;
        $db->table('group_privileges')
            ->insertOrIgnore([
                'group_id' => $buerocrat, 'privilege_id' => $editUser->id,
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'user.edit')
            ->delete();
    }
}
