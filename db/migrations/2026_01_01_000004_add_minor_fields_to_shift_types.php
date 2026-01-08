<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMinorFieldsToShiftTypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shift_types', function (Blueprint $table): void {
            $table->string('work_category', 1)
                ->default('A')
                ->comment('Work category for minor restrictions: A (all minors), B (teen only), C (adults only)')
                ->after('description');
            $table->boolean('allows_accompanying_children')
                ->default(false)
                ->comment('Whether parents can bring non-working children to shifts of this type')
                ->after('work_category');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shift_types', function (Blueprint $table): void {
            $table->dropColumn(['work_category', 'allows_accompanying_children']);
        });
    }
}
