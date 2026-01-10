<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Carbon\Carbon;
use Engelsystem\Helpers\Schedule\Day;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Day::class, '__construct')]
#[CoversMethod(Day::class, 'getDate')]
#[CoversMethod(Day::class, 'getStart')]
#[CoversMethod(Day::class, 'getEnd')]
#[CoversMethod(Day::class, 'getIndex')]
#[CoversMethod(Day::class, 'getRooms')]
class DayTest extends TestCase
{
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
        $this->assertEquals([], $day->getRooms());

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
        $this->assertEquals($rooms, $day->getRooms());
    }
}
