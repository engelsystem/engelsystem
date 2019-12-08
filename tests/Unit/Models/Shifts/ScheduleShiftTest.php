<?php

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleShiftTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Models\Shifts\ScheduleShift::schedule
     */
    public function testScheduleShifts()
    {
        $schedule = new Schedule(['url' => 'https://lorem.ipsum/schedule.xml']);
        $schedule->save();

        $scheduleShift = new ScheduleShift(['shift_id' => 1, 'guid' => 'a']);
        $scheduleShift->schedule()->associate($schedule);
        $scheduleShift->save();

        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = (new ScheduleShift())->find(1);
        $this->assertInstanceOf(BelongsTo::class, $scheduleShift->schedule());
        $this->assertEquals($schedule->id, $scheduleShift->schedule->id);
    }

    /**
     * Prepare test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
    }
}
