<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class GoodieManagerShowUserInfo extends Migration
{
    protected int $groupId = 50;
    protected string $privilegeName = 'user.info.show';

    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $privilege = $this->getPrivilege($this->privilegeName);

        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->groupId, 'privilege_id' => $privilege->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $privilege = $this->getPrivilege($this->privilegeName);

        $db->table('group_privileges')
            ->where('group_id', $this->groupId)
            ->where('privilege_id', $privilege->id)
            ->delete();
    }

    protected function getPrivilege(string $name): mixed
    {
        return $this->schema->getConnection()
            ->table('privileges')
            ->where('name', $name)
            ->first();
    }
}
