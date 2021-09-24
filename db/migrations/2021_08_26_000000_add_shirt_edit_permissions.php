<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddShirtEditPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        $db->table('Privileges')
            ->insert(['name' => 'user.edit.shirt', 'desc' => 'Edit user shirts']);

        $shiftCoordinator = -40;
        $shirtManager = -30;

        $userEditShirt = $db->table('Privileges')
            ->where('name', 'user.edit.shirt')
            ->get(['id'])->first();
        $adminArrive = $db->table('Privileges')
            ->where('name', 'admin_arrive')
            ->get(['id'])->first();

        $db->table('GroupPrivileges')
            ->insertOrIgnore([
                ['group_id' => $shiftCoordinator, 'privilege_id' => $userEditShirt->id],
                ['group_id' => $shirtManager, 'privilege_id' => $userEditShirt->id],
                ['group_id' => $shirtManager, 'privilege_id' => $adminArrive->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        $db->table('Privileges')
            ->where(['name' => 'user.edit.shirt'])
            ->delete();

        $shirtManager = -30;
        $adminArrive = $db->table('Privileges')
            ->where('name', 'admin_arrive')
            ->get(['id'])->first();

        $db->table('GroupPrivileges')
            ->where(['group_id' => $shirtManager, 'privilege_id' => $adminArrive->id])
            ->delete();
    }
}
