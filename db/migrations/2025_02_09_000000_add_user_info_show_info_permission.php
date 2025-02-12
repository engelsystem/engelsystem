<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddUserInfoShowInfoPermission extends Migration
{
    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $adminArriveId = $this->getPrivilegeId('admin_arrive');

        $this->db->table('privileges')
            ->insertOrIgnore([
                'name' => 'user.info.hint',
                'description' => 'Show hint that user info exists',
            ]);
        $userInfoShowInfoId = $this->getPrivilegeId('user.info.hint');

        $groups = $this->db->table('group_privileges')
            ->select('group_id')
            ->where('privilege_id', $adminArriveId)
            ->get('group_id');

        $insertValues = [];
        foreach ($groups as $group) {
            $insertValues[] = ['group_id' => $group->group_id, 'privilege_id' => $userInfoShowInfoId];
        }

        $this->db->table('group_privileges')
            ->insertOrIgnore($insertValues);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'user.info.hint')
            ->delete();
    }

    private function getPrivilegeId(string $privilege): int
    {
        return  $this->db->table('privileges')
            ->where('name', $privilege)
            ->get(['id'])
            ->first()
            ->id;
    }
}
