<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDectToRooms extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table(
            'rooms',
            function (Blueprint $table): void {
                $table->text('dect')->nullable()->after('description');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table(
            'rooms',
            function (Blueprint $table): void {
                $table->dropColumn('dect');
            }
        );
    }
}
