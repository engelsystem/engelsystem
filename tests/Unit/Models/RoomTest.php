<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;

class RoomTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Room::shifts
     */
    public function testShifts(): void
    {
        $room = new Room(['name' => 'Test room']);
        $room->save();

        /** @var Shift $shift */
        Shift::factory()->create(['room_id' => 1]);

        $room = Room::find(1);
        $this->assertCount(1, $room->shifts);
    }

    /**
     * @covers \Engelsystem\Models\Room::neededAngelTypes
     */
    public function testNeededAngelTypes(): void
    {
        /** @var Collection|Room[] $shifts */
        $shifts = Room::factory(3)->create();

        $this->assertCount(0, Room::find(1)->neededAngelTypes);

        (NeededAngelType::factory()->make(['room_id' => $shifts[0]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['room_id' => $shifts[0]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['room_id' => $shifts[1]->id, 'shift_id' => null]))->save();
        (NeededAngelType::factory()->make(['room_id' => $shifts[2]->id, 'shift_id' => null]))->save();

        $this->assertCount(2, Room::find(1)->neededAngelTypes);
        $this->assertEquals(1, Room::find(1)->neededAngelTypes[0]->id);
        $this->assertEquals(2, Room::find(1)->neededAngelTypes[1]->id);
        $this->assertEquals(3, Room::find(2)->neededAngelTypes->first()->id);
        $this->assertEquals(4, Room::find(3)->neededAngelTypes->first()->id);
    }
}
