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
        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_important', 'is_highlighted');
        });

        $this->schema->getConnection()
            ->table('privileges')
            ->where('name', 'news.important')
            ->update(['name' => 'news.highlight', 'description' => 'Highlight News']);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_highlighted', 'is_important');
        });

        $this->schema->getConnection()
            ->table('privileges')
            ->where('name', 'news.highlight')
            ->update(['name' => 'news.important', 'description' => 'Make News Important']);
    }
}
