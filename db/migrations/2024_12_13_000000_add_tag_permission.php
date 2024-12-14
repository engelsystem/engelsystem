<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddTagPermission extends Migration
{
    protected int $shiftCoordinatorId = 60;
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
            ->insert([
                'name' => 'tag.edit',
                'description' => 'Edit tags',
            ]);
        $privilegeId = $this->db->table('privileges')
            ->where('name', 'tag.edit')
            ->get(['id'])
            ->first()
            ->id;

        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->shiftCoordinatorId, 'privilege_id' => $privilegeId],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'tag.edit')
            ->delete();
    }
}
