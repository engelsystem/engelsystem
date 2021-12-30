<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class RemoveAdminNewsHtmlPrivilege extends Migration
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

        // Delete unused privileges
        $connection->delete('
                DELETE FROM `Privileges`
                WHERE `name` = \'admin_news_html\'
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

        $connection->insert('
                INSERT INTO `Privileges` (`name`, `desc`)
                VALUES (\'admin_news_html\', \'Use HTML in news\')
            ');
    }
}
