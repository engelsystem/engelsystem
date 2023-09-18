<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class PermissionsRenameNewsImportantToNewsHighlight extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'news.important')
            ->update(['name' => 'news.highlight', 'description' => 'Highlight News']);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->where('name', 'news.highlight')
            ->update(['name' => 'news.important', 'description' => 'Make News Important']);
    }
}
