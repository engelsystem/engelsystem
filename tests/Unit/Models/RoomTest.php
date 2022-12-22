<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Shift;

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
}
