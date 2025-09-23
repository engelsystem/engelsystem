<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class WorklogRenameCommentToDescription extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('worklogs', function (Blueprint $table): void {
            $table->renameColumn('comment', 'description');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('worklogs', function (Blueprint $table): void {
            $table->renameColumn('description', 'comment');
        });
    }
}
