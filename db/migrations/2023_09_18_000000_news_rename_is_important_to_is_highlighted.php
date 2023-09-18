<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class NewsRenameIsImportantToIsHighlighted extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_important', 'is_highlighted');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('news', function (Blueprint $table): void {
            $table->renameColumn('is_highlighted', 'is_important');
        });
    }
}
