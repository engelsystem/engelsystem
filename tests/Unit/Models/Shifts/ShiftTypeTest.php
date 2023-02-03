<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\Shift;
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

    /**
     * @covers \Engelsystem\Models\Shifts\ShiftType::shifts
     */
    public function testShifts(): void
    {
        $shiftType = new ShiftType(['name' => 'Another type', 'description' => '']);
        $shiftType->save();

        Shift::factory()->create(['shift_type_id' => 1]);

        $this->assertCount(1, ShiftType::find(1)->shifts);
    }
}
