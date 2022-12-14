<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use stdClass;

class FixMissingArrivalDates extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        /** @var stdClass[] $states */
        $states = $connection
            ->table('users_state')
            ->where('arrived', true)
            ->where('arrival_date', null)
            ->get();

        foreach ($states as $state) {
            /** @var stdClass $personalData */
            $personalData = $connection
                ->table('users_personal_data')
                ->where('user_id', $state->user_id)
                ->first();
            $state->arrival_date = $personalData->planned_arrival_date;
            $connection->table('users_state')
                ->update((array)$state);
        }
    }

    /**
     * Down is not possible and not needed since this is a bugfix.
     */
    public function down(): void
    {
    }
}
