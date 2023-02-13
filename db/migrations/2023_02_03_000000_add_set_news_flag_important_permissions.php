<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class AddSetNewsFlagImportantPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert(['name' => 'news.important', 'description' => 'Make News Important']);

        $newsImportant = $db->table('privileges')
            ->where('name', 'news.important')
            ->get(['id'])
            ->first();

        $buerocrat = 80;
        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $buerocrat, 'privilege_id' => $newsImportant->id],
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where(['name' => 'news.important'])
            ->delete();
    }
}
