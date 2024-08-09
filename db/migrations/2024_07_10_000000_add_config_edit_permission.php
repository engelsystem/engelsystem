<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddConfigEditPermission extends Migration
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
            ->where('name', 'admin_event_config')
            ->update([
                'name' => 'config.edit',
                'description' => 'Edit the application configuration',
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'config.edit')
            ->update([
                'name' => 'admin_event_config',
                'description' => 'Allow editing event config',
            ]);
    }
}
