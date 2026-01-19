<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Shifts;

use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Test\Unit\Models\ModelTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ShiftType::class, 'neededAngelTypes')]
#[CoversMethod(ShiftType::class, 'schedules')]
#[CoversMethod(ShiftType::class, 'shifts')]
class ShiftTypeTest extends ModelTestCase
{
    public function testNeededAngelTypes(): void
    {
        $shiftType = new ShiftType(['name' => 'Another type', 'description' => '']);
        $shiftType->save();

        NeededAngelType::factory()->create(['shift_type_id' => 1]);

        $this->assertCount(1, ShiftType::find(1)->neededAngelTypes);
    }

    public function testSchedules(): void
    {
        ShiftType::factory()->create();
        $shiftType = new ShiftType(['name' => 'Test type', 'description' => 'Foo bar baz']);
        $shiftType->save();

        Schedule::factory()->create(['shift_type' => 2]);
        Schedule::factory(2)->create(['shift_type' => 1]);

        $this->assertCount(2, ShiftType::find(1)->schedules);
    }

    public function testShifts(): void
    {
        $shiftType = new ShiftType(['name' => 'Another type', 'description' => '']);
        $shiftType->save();

        Shift::factory()->create(['shift_type_id' => 1]);

        $this->assertCount(1, ShiftType::find(1)->shifts);
    }
}
