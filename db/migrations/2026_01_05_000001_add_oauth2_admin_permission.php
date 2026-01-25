<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddOauth2AdminPermission extends Migration
{
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        $connection->table('privileges')->insert([
            'name' => 'oauth2.clients.edit',
            'description' => 'Manage OAuth2 clients',
        ]);

        $privilegeId = $connection->table('privileges')
            ->where('name', 'oauth2.clients.edit')
            ->value('id');

        // Assign to Developer group (id: 90)
        $connection->table('group_privileges')->insert([
            'group_id' => 90,
            'privilege_id' => $privilegeId,
        ]);
    }

    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $privilegeId = $connection->table('privileges')
            ->where('name', 'oauth2.clients.edit')
            ->value('id');

        if ($privilegeId) {
            $connection->table('group_privileges')
                ->where('privilege_id', $privilegeId)
                ->delete();

            $connection->table('privileges')
                ->where('id', $privilegeId)
                ->delete();
        }
    }
}
