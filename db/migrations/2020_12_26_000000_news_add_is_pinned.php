<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class NewsAddIsPinned extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('news', function (Blueprint $table): void {
            $table->boolean('is_pinned')->default(false)->after('is_meeting');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('news', function (Blueprint $table): void {
            $table->dropColumn('is_pinned');
        });
    }
}
