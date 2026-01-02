<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMinorFieldsToShifts extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->boolean('requires_supervisor_for_minors')
                ->default(true)
                ->after('updated_by');
            $table->text('minor_supervision_notes')
                ->nullable()
                ->after('requires_supervisor_for_minors');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->dropColumn(['requires_supervisor_for_minors', 'minor_supervision_notes']);
        });
    }
}
