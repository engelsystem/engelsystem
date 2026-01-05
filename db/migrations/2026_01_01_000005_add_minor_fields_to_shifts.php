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
        // Make migration idempotent by checking each column before adding
        if (!$this->schema->hasColumn('shifts', 'requires_supervisor_for_minors')) {
            $this->schema->table('shifts', function (Blueprint $table): void {
                $table->boolean('requires_supervisor_for_minors')
                    ->default(true)
                    ->after('updated_by');
            });
        }

        if (!$this->schema->hasColumn('shifts', 'minor_supervision_notes')) {
            $this->schema->table('shifts', function (Blueprint $table): void {
                $table->text('minor_supervision_notes')
                    ->nullable()
                    ->after('requires_supervisor_for_minors');
            });
        }

        if (!$this->schema->hasColumn('shifts', 'work_category_override')) {
            $this->schema->table('shifts', function (Blueprint $table): void {
                $table->string('work_category_override', 1)
                    ->nullable()
                    ->comment('Override work category: A/B/C, NULL=inherit')
                    ->after('minor_supervision_notes');
            });
        }

        if (!$this->schema->hasColumn('shifts', 'allows_accompanying_children_override')) {
            $this->schema->table('shifts', function (Blueprint $table): void {
                $table->boolean('allows_accompanying_children_override')
                    ->nullable()
                    ->comment('Override ShiftType accompanying children setting, NULL = inherit')
                    ->after('work_category_override');
            });
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->dropColumn([
                'requires_supervisor_for_minors',
                'minor_supervision_notes',
                'work_category_override',
                'allows_accompanying_children_override',
            ]);
        });
    }
}
