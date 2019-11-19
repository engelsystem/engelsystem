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
        $connection = $this->schema->getConnection();

        // Add api permissions
        $connection->insert(
            '
                INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES (NULL, \'view_api\', \'API accessible.\');
            ',
            [
                -40, // Shift Coordinator
                43,  // admin_user_worklog
            ]
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        // Remove view_api permission
        $this->schema->getConnection()->delete(
            'DELETE FROM `Privileges`
                WHERE `name` = \'view_api\'
                )'
        );
    }
}
