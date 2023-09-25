<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class NewsRenameImportantToHighlight extends Migration
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

        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_important', 'is_highlighted');
        });
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

        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_highlighted', 'is_important');
        });
    }
}
