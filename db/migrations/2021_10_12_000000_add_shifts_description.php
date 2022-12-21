<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddShiftsDescription extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('Shifts')) {
            return;
        }

        $this->schema->table(
            'Shifts',
            function (Blueprint $table): void {
                $table->text('description')->nullable()->after('shifttype_id');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('Shifts')) {
            return;
        }

        $this->schema->table(
            'Shifts',
            function (Blueprint $table): void {
                $table->dropColumn('description');
            }
        );
    }
}
