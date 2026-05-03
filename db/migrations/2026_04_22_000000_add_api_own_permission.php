<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddApiOwnPermission extends Migration
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
        $this->db->table('privileges')
            ->insertOrIgnore([
                'name' => 'api.own',
                'description' => 'Access own data via the API',
            ]);

        $apiOwnId = $this->getPrivilegeId('api.own');

        $groups = $this->db->table('groups')->select('id')->get();

        $insertValues = [];
        foreach ($groups as $group) {
            $insertValues[] = ['group_id' => $group->id, 'privilege_id' => $apiOwnId];
        }

        if (!empty($insertValues)) {
            $this->db->table('group_privileges')
                ->insertOrIgnore($insertValues);
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'api.own')
            ->delete();
    }

    private function getPrivilegeId(string $privilege): int
    {
        return $this->db->table('privileges')
            ->where('name', $privilege)
            ->get(['id'])
            ->first()
            ->id;
    }
}
