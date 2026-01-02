<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddWorkCategoryToAngelTypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->string('work_category', 1)
                ->nullable()
                ->comment('Work category for minor restrictions: A (light), B (medium), C (unrestricted)')
                ->after('hide_on_shift_view');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->dropColumn('work_category');
        });
    }
}
