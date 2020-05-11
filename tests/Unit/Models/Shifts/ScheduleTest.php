<?php

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Test\Unit\Models\ModelTest;

class ScheduleTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\Schedule::scheduleShifts
     */
    public function testScheduleShifts()
    {
        $schedule = new Schedule(['url' => 'https://foo.bar/schedule.xml']);
        $schedule->save();

        (new ScheduleShift(['shift_id' => 1, 'schedule_id' => $schedule->id, 'guid' => 'a']))->save();
        (new ScheduleShift(['shift_id' => 2, 'schedule_id' => $schedule->id, 'guid' => 'b']))->save();
        (new ScheduleShift(['shift_id' => 3, 'schedule_id' => $schedule->id, 'guid' => 'c']))->save();

        $this->assertCount(3, $schedule->scheduleShifts);
    }
}
