<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CreateVoucherEditPermission extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        $db->table('Privileges')->insert([
            ['name' => 'voucher.edit', 'desc' => 'Edit vouchers'],
        ]);
        $db->table('Groups')->insert([
            ['Name' => 'Voucher Angel', 'UID' => -26],
        ]);

        $shiftCoordinatorGroup = -40;
        $editId = $db->table('Privileges')->where('name', 'voucher.edit')->first()->id;
        $arriveId = $db->table('Privileges')->where('name', 'admin_arrive')->first()->id;
        $db->table('GroupPrivileges')->insert([
            ['group_id' => $shiftCoordinatorGroup, 'privilege_id' => $editId],
            ['group_id' => -26, 'privilege_id' => $editId],
            ['group_id' => -26, 'privilege_id' => $arriveId],
        ]);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        $db->table('Privileges')
            ->where('name', 'voucher.edit')
            ->delete();
        $db->table('Groups')
            ->where('UID', -26)
            ->delete();
    }
}
