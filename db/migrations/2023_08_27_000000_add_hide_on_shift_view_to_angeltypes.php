<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHideOnShiftViewToAngeltypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->boolean('hide_on_shift_view')->default(false)->after('hide_register');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->dropColumn('hide_on_shift_view');
        });
    }
}
