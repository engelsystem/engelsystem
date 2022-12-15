<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddAngelSupporterPermissions extends Migration
{
    /** @var string[] */
    protected array $data = [
        '2-Engel',
        'shiftentry_edit_angeltype_supporter',
    ];

    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('GroupPrivileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        if (!empty($db->select($this->getQuery('SELECT *'), $this->data))) {
            return;
        }

        // Add permissions to angels to edit angels if they are angeltype supporters
        $db->insert(
            '
                INSERT IGNORE INTO GroupPrivileges (group_id, privilege_id)
                VALUES ((SELECT UID FROM `Groups` WHERE `name` = ?), (SELECT id FROM `Privileges` WHERE `name` = ?))
            ',
            $this->data
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

        // Remove permission from angels
        $this->schema->getConnection()->delete(
            $this->getQuery('DELETE'),
            $this->data
        );
    }

    protected function getQuery(string $type): string
    {
        return sprintf('
                %s FROM GroupPrivileges
                WHERE group_id = (SELECT UID FROM `Groups` WHERE `name` = ?)
                AND privilege_id = (SELECT id FROM `Privileges` WHERE `name` = ?)
        ', $type);
    }
}
