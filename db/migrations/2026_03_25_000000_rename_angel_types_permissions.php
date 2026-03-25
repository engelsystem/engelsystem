<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class RenameAngelTypesPermissions extends Migration
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
            ->where('name', 'angeltypes')
            ->update([
                'name' => 'angeltypes.view',
                'description' => 'View angel types',
            ]);
        $this->db->table('privileges')
            ->where('name', 'admin_angeltypes')
            ->update([
                'name' => 'angeltypes.edit',
                'description' => 'Edit angel types'
            ]);
        $this->db->table('privileges')
            ->where('name', 'admin_user_angeltypes')
            ->update([
                'name' => 'userangeltypes.edit',
                'description' => 'Edit user angel types (confirm and promote to supporter)',
            ]);
        $this->db->table('privileges')
            ->where('name', 'angeltype.goodie.list')
            ->update([
                'name' => 'angeltypes.goodie.list',
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'angeltypes.view')
            ->update([
                'name' => 'angeltypes',
                'description' => 'View angeltypes',
            ]);
        $this->db->table('privileges')
            ->where('name', 'angeltypes.edit')
            ->update([
                'name' => 'admin_angeltypes',
                'description' => 'Edit angel types'
            ]);
        $this->db->table('privileges')
            ->where('name', 'userangeltypes.edit')
            ->update([
                'name' => 'admin_user_angeltypes',
                'description' => 'Confirm restricted angel types',
            ]);
        $this->db->table('privileges')
            ->where('name', 'angeltypes.goodie.list')
            ->update([
                'name' => 'angeltype.goodie.list',
            ]);
    }
}
