<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class ChangeEditShirtRequireBureaucrat extends Migration
{
    protected int $bureaucrat = 80;

    protected int $shiCo = 60;

    protected int $editShirt;

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();

        $this->editShirt = $this->db->table('privileges')
            ->where('name', 'user.edit.shirt')
            ->get(['id'])
            ->first()->id;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->movePermission($this->editShirt, $this->shiCo, $this->bureaucrat);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->movePermission($this->editShirt, $this->bureaucrat, $this->shiCo);
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
