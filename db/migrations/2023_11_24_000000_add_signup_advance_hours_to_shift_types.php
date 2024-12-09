<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSignupAdvanceHoursToShiftTypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shift_types', function (Blueprint $table): void {
            $table->float('signup_advance_hours')->nullable()->default(null)->after('description');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shift_types', function (Blueprint $table): void {
            $table->dropColumn('signup_advance_hours');
        });
    }
}
