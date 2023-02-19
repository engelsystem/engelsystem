<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class RenameShirtManager extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('groups')
            ->where('name', 'Shirt Manager')
            ->update(['name' => 'Goodie Manager']);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('groups')
            ->where('name', 'Goodie Manager')
            ->update(['name' => 'Shirt Manager']);
    }
}
