<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddAngeltypeGoodieListPermission extends Migration
{
    protected Connection $db;
    protected int $goodieManager = 50;

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
                'name' => 'angeltype.goodie.list',
                'description' => 'Add edit goodies to angel type view',
            ]);

        $angeltypeGoodieList = $this->db->table('privileges')
            ->where('name', 'angeltype.goodie.list')
            ->get(['id'])
            ->first();

        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->goodieManager, 'privilege_id' => $angeltypeGoodieList->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'angeltype.goodie.list')
            ->delete();
    }
}
