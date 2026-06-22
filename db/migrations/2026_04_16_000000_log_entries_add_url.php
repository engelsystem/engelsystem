<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class LogEntriesAddUrl extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('log_entries', function (Blueprint $table): void {
            $table->text('url')->nullable()->default(null)->after('message');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('log_entries', function (Blueprint $table): void {
            $table->dropColumn('url');
        });
    }
}
