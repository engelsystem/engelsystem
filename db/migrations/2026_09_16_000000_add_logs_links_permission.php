<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddLogsLinksPermission extends Migration
{
    protected int $developerId = 90;
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
        $privilegeId = $this->db->table('privileges')
            ->insertGetId([
                'name' => 'logs.links',
                'description' => 'Show links to pages in log',
            ]);

        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->developerId, 'privilege_id' => $privilegeId],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'logs.links')
            ->delete();
    }
}
