<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class RemoveAdminNewsHtmlPrivilege extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $connection = $this->schema->getConnection();

        // Delete unused privilege
        $connection->delete(
            'DELETE FROM `Privileges` WHERE `name` = ?',
            ['admin_news_html']
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $connection = $this->schema->getConnection();
        $connection->insert(
            'INSERT INTO `Privileges` (`name`, `desc`) VALUES (?, ?)',
            ['admin_news_html', 'Use HTML in news']
        );

        // Add permissions to news admins to edit html news
        $connection->insert(
            '
                INSERT IGNORE INTO GroupPrivileges (group_id, privilege_id)
                VALUES ((SELECT UID FROM `Groups` WHERE `name` = ?), (SELECT id FROM `Privileges` WHERE `name` = ?))
            ',
            ['News Admin', 'admin_news_html']
        );
    }
}
