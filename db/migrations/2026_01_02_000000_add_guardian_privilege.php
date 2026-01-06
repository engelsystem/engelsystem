<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

/**
 * Add user_guardian privilege for guardian functionality.
 *
 * This privilege allows adult users to access the guardian dashboard
 * and manage linked minor accounts.
 */
class AddGuardianPrivilege extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();

        // Add the user_guardian privilege (insertOrIgnore for idempotency)
        $db->table('privileges')
            ->insertOrIgnore([
                'name' => 'user_guardian',
                'description' => 'Access guardian dashboard and manage linked minors',
            ]);

        $guardianPrivilege = $db->table('privileges')
            ->where('name', 'user_guardian')
            ->get(['id'])
            ->first();

        // Assign to Angel group (id=20) - all regular users can be guardians if they're adults
        $angelGroup = 20;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $angelGroup, 'privilege_id' => $guardianPrivilege->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where(['name' => 'user_guardian'])
            ->delete();
    }
}
