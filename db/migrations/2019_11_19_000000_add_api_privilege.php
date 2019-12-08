<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddApiPrivilege extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }
        $connection = $this->schema->getConnection();
        // Add api permissions
        $connection->table('Privileges')->insert(
            [
                'name' => 'view_api',
                'desc' => 'API accessible.'
            ]
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }
        $connection = $this->schema->getConnection();
        // Remove view_api permission
        $connection->table('Privileges')->where('name', '=', 'view_api')->delete();
    }
}
