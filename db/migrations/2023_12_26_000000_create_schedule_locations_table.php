<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScheduleLocationsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the new table
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('schedule_locations', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'schedules');
            $this->references($table, 'locations');

            $table->index(['schedule_id', 'location_id']);
        });

        $scheduleLocations = $connection
            ->table('schedule_shift')
            ->select(['schedules.id AS schedule_id', 'locations.id AS location_id'])
            ->leftJoin('schedules', 'schedules.id', 'schedule_shift.schedule_id')
            ->leftJoin('shifts', 'shifts.id', 'schedule_shift.shift_id')
            ->leftJoin('locations', 'locations.id', 'shifts.location_id')
            ->groupBy(['schedules.id', 'locations.id'])
            ->get();

        foreach ($scheduleLocations as $scheduleLocation) {
            $connection->table('schedule_locations')
                ->insert((array) $scheduleLocation);
        }
    }

    /**
     * Drops the table
     */
    public function down(): void
    {
        $this->schema->drop('schedule_locations');
    }
}
