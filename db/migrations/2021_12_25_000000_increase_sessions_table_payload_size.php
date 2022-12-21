<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class IncreaseSessionsTablePayloadSize extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('sessions', function (Blueprint $table): void {
            $table->mediumText('payload')->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('sessions', function (Blueprint $table): void {
            $table->text('payload')->change();
        });
    }
}
