<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CreateWelcomeAngelPermissionsGroup extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('Groups')) {
            return;
        }

        $db = $this->schema->getConnection();

        $db
            ->table('Groups')
            ->insert([
                'UID'  => -25,
                'Name' => 'Welcome Angel',
            ]);

        $privilege = $db->table('Privileges')
            ->where('name', 'admin_arrive')
            ->first();

        $db->table('GroupPrivileges')
            ->insert([
                'group_id'     => -25,
                'privilege_id' => $privilege->id,
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('Groups')) {
            return;
        }

        $this->schema->getConnection()
            ->table('Groups')
            ->where('UID', -25)
            ->delete();
    }
}
