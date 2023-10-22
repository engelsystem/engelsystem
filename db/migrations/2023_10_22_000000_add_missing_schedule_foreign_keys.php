<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMissingScheduleForeignKeys extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $schemaManager = $this->schema->getConnection()->getDoctrineSchemaManager();

        $hasShiftTypeReference = $schemaManager->introspectTable('schedules')
            ->hasIndex('schedules_shift_type_foreign');
        if (!$hasShiftTypeReference) {
            $this->schema->table('schedules', function (Blueprint $table): void {
                $table->unsignedInteger('shift_type')->change();
                $this->addReference($table, 'shift_type', 'shift_types');
            });
        }

        $hasShiftIdReference = $schemaManager->introspectTable('schedule_shift')
            ->hasIndex('schedule_shift_schedule_id_foreign');
        if (!$hasShiftIdReference) {
            $this->schema->table('schedule_shift', function (Blueprint $table): void {
                $table->unsignedInteger('shift_id')->change();
                $this->addReference($table, 'shift_id', 'shifts');
            });
        }
    }
}
