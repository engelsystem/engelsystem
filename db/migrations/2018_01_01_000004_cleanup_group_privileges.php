<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CleanupGroupPrivileges extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $connection = $this->schema->getConnection();

        // Add permissions to shikos to assign worklog entries
        $connection->insert(
            '
                INSERT INTO `GroupPrivileges` (`group_id`, `privilege_id`)
                VALUES (?, ?)
            ',
            [
                -40, // Shift Coordinator
                43,  // admin_user_worklog
            ]
        );

        // Delete unused privileges
        $connection->delete('
                DELETE FROM `Privileges`
                WHERE `name` IN(
                    \'user_wakeup\',
                    \'faq\',
                    \'credits\'
                )
            ');
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        // Add permissions to shikos to assign worklog entries
        $this->schema->getConnection()->delete(
            '
                DELETE FROM `GroupPrivileges`
                WHERE
                  group_id = ?
                  AND privilege_id = ?
            ',
            [
                -40, // Shift Coordinator
                43,  // admin_user_worklog
            ]
        );
    }
}
