<?php

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Models\Shifts\Schedule;
use Illuminate\Database\Schema\Blueprint;

class AddNameMinutesAndTimestampsToSchedules extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'schedules',
            function (Blueprint $table) {
                $table->string('name')->default('')->after('id');
                $table->integer('shift_type')->default(0)->after('name');
                $table->integer('minutes_before')->default(0)->after('shift_type');
                $table->integer('minutes_after')->default(0)->after('minutes_before');
                $table->timestamps();
            }
        );

        Schedule::query()
            ->update([
                'created_at'     => Carbon::now(),
                'minutes_before' => 15,
                'minutes_after'  => 15,
            ]);

        $this->schema->table(
            'schedules',
            function (Blueprint $table) {
                $table->string('name')->default(null)->change();
                $table->integer('shift_type')->default(null)->change();
                $table->integer('minutes_before')->default(null)->change();
                $table->integer('minutes_after')->default(null)->change();
            }
        );

        // Add legacy reference
        if ($this->schema->hasTable('ShiftTypes')) {
            $connection = $this->schema->getConnection();
            $query = $connection
                ->table('Shifts')
                ->select('Shifts.shifttype_id')
                ->join('schedule_shift', 'Shifts.SID', 'schedule_shift.shift_id')
                ->where('schedule_shift.schedule_id', $connection->raw('schedules.id'))
                ->limit(1);

            Schedule::query()
                ->update(['shift_type' => $connection->raw('(' . $query->toSql() . ')')]);

            $this->schema->table(
                'schedules',
                function (Blueprint $table) {
                    $this->addReference($table, 'shift_type', 'ShiftTypes');
                }
            );
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'schedules',
            function (Blueprint $table) {
                $table->dropForeign('schedules_shift_type_foreign');
                $table->dropColumn('name');
                $table->dropColumn('shift_type');
                $table->dropColumn('minutes_before');
                $table->dropColumn('minutes_after');
                $table->dropTimestamps();
            }
        );
    }
}
