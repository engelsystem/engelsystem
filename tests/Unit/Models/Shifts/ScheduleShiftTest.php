<?php

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Test\Unit\Models\ModelTest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleShiftTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\ScheduleShift::schedule
     */
    public function testScheduleShifts(): void
    {
        $schedule = new Schedule([
            'url' => 'https://lorem.ipsum/schedule.xml',
            'name' => 'Test',
            'shift_type' => 0,
            'minutes_before' => 15,
            'minutes_after' => 15,
        ]);
        $schedule->save();

        $scheduleShift = new ScheduleShift(['shift_id' => 1, 'guid' => 'a']);
        $scheduleShift->schedule()->associate($schedule);
        $scheduleShift->save();

        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = (new ScheduleShift())->find(1);
        $this->assertInstanceOf(BelongsTo::class, $scheduleShift->schedule());
        $this->assertEquals($schedule->id, $scheduleShift->schedule->id);
    }
}
