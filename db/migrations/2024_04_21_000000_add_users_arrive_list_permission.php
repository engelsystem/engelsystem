<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddUsersArriveListPermission extends Migration
{
    protected int $voucher = 35;

    protected int $arrive;

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();

        $this->arrive = $this->db->table('privileges')
            ->where('name', 'admin_arrive')
            ->get(['id'])
            ->first()->id;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->db->table('privileges')
            ->insert([
                'name' => 'users.arrive.list', 'description' => 'View arrive angels list',
            ]);

        $arriveList = $this->db->table('privileges')
            ->where('name', 'users.arrive.list')
            ->get(['id'])
            ->first()->id;

        // Goodie Manager, Shift Coordinator, Voucher Angel, Welcome Angel
        $groups = [50, 60, 35, 30];
        foreach ($groups as $group) {
            $this->db->table('group_privileges')
                ->insertOrIgnore([
                    ['group_id' => $group, 'privilege_id' => $arriveList],
                ]);
        }

        $this->db->table('group_privileges')
            ->where(['group_id' => $this->voucher, 'privilege_id' => $this->arrive])
            ->delete();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('privileges')
            ->where('name', 'users.arrive.list')
            ->delete();

        $this->db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->voucher, 'privilege_id' => $this->arrive],
            ]);
    }
}
