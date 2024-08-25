<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class RenameLocationsPermissions extends Migration
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
            ->where('name', 'view_locations')
            ->update([
                'name' => 'locations.view',
                'description' => 'View locations',
            ]);
        $this->db->table('privileges')
            ->where('name', 'admin_locations')
            ->update([
                'name' => 'locations.edit',
                'description' => 'Edit locations',
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'locations.view')
            ->update([
                'name' => 'view_locations',
                'description' => 'User can view locations',
            ]);
        $this->db->table('privileges')
            ->where('name', 'locations.edit')
            ->update([
                'name' => 'admin_locations',
                'description' => 'Manage locations',
            ]);
    }
}
