<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CreateApiPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')->insert([
            ['name' => 'api', 'description' => 'Use the API'],
        ]);
        $db->table('groups')->insert([
            ['id' => 40, 'name' => 'API'],
        ]);

        $bureaucratGroup = 80;
        $apiId = $db->table('privileges')->where('name', 'api')->first()->id;
        $db->table('group_privileges')->insert([
            ['group_id' => $bureaucratGroup, 'privilege_id' => $apiId],
            ['group_id' => 40, 'privilege_id' => $apiId],
        ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'api')
            ->delete();
        $db->table('groups')
            ->where('id', 40)
            ->delete();
    }
}
