<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMissingScheduleShiftScheduleForeignKey extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $hasScheduleIdReference = $this->schema->hasIndex('schedule_shift', 'schedule_shift_schedule_id_foreign');
        if (!$hasScheduleIdReference) {
            $this->schema->table('schedule_shift', function (Blueprint $table): void {
                $this->addReference($table, 'schedule_id', 'schedules');
            });
        }
    }
}
