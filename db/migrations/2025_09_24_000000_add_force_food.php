<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddForceFood extends Migration
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
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->boolean('force_food')->after('force_active');
        });
        $this->db->table('privileges')->insertOrIgnore([
            'name' => 'user.ff.edit',
            'description' => 'Edit user force food state',
        ]);
        $permissionId = $this->db->table('privileges')
            ->where('name', 'user.ff.edit')
            ->get(['id'])
            ->first()->id;

        // add permission to group Shift Coordinator
        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->shiftCoordinatorId, 'privilege_id' => $permissionId],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropColumn('force_food');
        });
        $this->db->table('privileges')->where('name', 'user.ff.edit')->delete();
    }
}
