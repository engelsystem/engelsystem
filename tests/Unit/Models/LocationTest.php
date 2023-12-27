<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;

class LocationTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Location::activeForSchedules
     */
    public function testActiveForSchedules(): void
    {
        $location = new Location(['name' => 'Test location']);
        $location->save();

        $schedule = Schedule::factory()->create();
        $location->activeForSchedules()->attach($schedule);

        $location = Location::find($location->id);
        $this->assertCount(1, $location->activeForSchedules);
    }

    /**
     * @covers \Engelsystem\Models\Location::shifts
     */
    public function testShifts(): void
    {
        $location = new Location(['name' => 'Test location']);
        $location->save();

        /** @var Shift $shift */
        Shift::factory()->create(['location_id' => 1]);

        $location = Location::find(1);
        $this->assertCount(1, $location->shifts);
    }

    /**
     * @covers \Engelsystem\Models\Location::neededAngelTypes
     */
    public function testNeededAngelTypes(): void
    {
        /** @var Collection|Location[] $shifts */
        $shifts = Location::factory(3)->create();

        $this->assertCount(0, Location::find(1)->neededAngelTypes);

        (NeededAngelType::factory()->make(['location_id' => $shifts[0]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['location_id' => $shifts[0]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['location_id' => $shifts[1]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['location_id' => $shifts[2]->id, 'shift_id' => null]))->save();

        $this->assertCount(2, Location::find(1)->neededAngelTypes);
        $this->assertEquals(1, Location::find(1)->neededAngelTypes[0]->id);
        $this->assertEquals(2, Location::find(1)->neededAngelTypes[1]->id);
        $this->assertEquals(3, Location::find(2)->neededAngelTypes->first()->id);
        $this->assertEquals(4, Location::find(3)->neededAngelTypes->first()->id);
    }
}
