<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddShifttypesEditPermissionAndShifttypesRequiresShico extends Migration
{
    protected int $bureaucrat = 80;
    protected int $shiCo = 60;

    protected int $shifttypes;

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();

        $this->shifttypes = $this->db->table('privileges')
            ->where('name', 'shifttypes')
            ->get(['id'])
            ->first()->id;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                'name' => 'shifttypes.edit', 'description' => 'Edit shift types',
            ]);

        $editShifttypes = $db->table('privileges')
            ->where('name', 'shifttypes.edit')
            ->get(['id'])
            ->first();

        $this->movePermission($this->shifttypes, $this->bureaucrat, $this->shiCo);

        $db->table('group_privileges')
            ->insertOrIgnore([
                'group_id' => $this->bureaucrat, 'privilege_id' => $editShifttypes->id,
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'shifttypes.edit')
            ->delete();

        $this->movePermission($this->shifttypes, $this->shiCo, $this->bureaucrat);
    }

    protected function movePermission(int $privilege, int $oldGroup, int $newGroup): void
    {
        $this->db->table('group_privileges')
            ->insertOrIgnore(['group_id' => $newGroup, 'privilege_id' => $privilege]);

        $this->db->table('group_privileges')
            ->where(['group_id' => $oldGroup, 'privilege_id' => $privilege])
            ->delete();
    }
}
