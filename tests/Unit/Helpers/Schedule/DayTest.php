<?php

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Carbon\Carbon;
use Engelsystem\Helpers\Schedule\Day;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Test\Unit\TestCase;

class DayTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\Day::__construct
     * @covers \Engelsystem\Helpers\Schedule\Day::getDate
     * @covers \Engelsystem\Helpers\Schedule\Day::getStart
     * @covers \Engelsystem\Helpers\Schedule\Day::getEnd
     * @covers \Engelsystem\Helpers\Schedule\Day::getIndex
     * @covers \Engelsystem\Helpers\Schedule\Day::getRoom
     */
    public function testCreate(): void
    {
        $day = new Day(
            '2000-01-01',
            new Carbon('2000-01-01T03:00:00+01:00'),
            new Carbon('2000-01-02T05:59:00+00:00'),
            1
        );
        $this->assertEquals('2000-01-01', $day->getDate());
        $this->assertEquals('2000-01-01T03:00:00+01:00', $day->getStart()->format(Carbon::RFC3339));
        $this->assertEquals('2000-01-02T05:59:00+00:00', $day->getEnd()->format(Carbon::RFC3339));
        $this->assertEquals(1, $day->getIndex());
        $this->assertEquals([], $day->getRoom());

        $rooms = [
            new Room('Foo'),
            new Room('Bar'),
        ];
        $day = new Day(
            '2001-01-01',
            new Carbon('2001-01-01T03:00:00+01:00'),
            new Carbon('2001-01-02T05:59:00+00:00'),
            1,
            $rooms
        );
        $this->assertEquals($rooms, $day->getRoom());
    }
}
