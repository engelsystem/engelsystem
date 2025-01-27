<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddWorklogNightshift extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('worklogs', function (Blueprint $table): void {
            $table->boolean('night_shift')->default(false)->after('worked_at');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('worklogs', function (Blueprint $table): void {
            $table->dropColumn('night_shift');
        });
    }
}
