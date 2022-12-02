<?php

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Test\Unit\Models\ModelTest;

class ShiftTypeTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Shifts\ShiftType::schedules
     */
    public function testSchedules(): void
    {
        $shiftType = new ShiftType(['name' => 'Test type', 'description' => 'Foo bar baz']);
        $shiftType->save();

        Schedule::factory()->create(['shift_type' => 2]);
        Schedule::factory(2)->create(['shift_type' => 1]);

        $this->assertCount(2, ShiftType::find(1)->schedules);
    }
}
