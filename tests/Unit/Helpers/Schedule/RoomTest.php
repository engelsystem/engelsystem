<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Test\Unit\TestCase;

class RoomTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\Room::__construct
     * @covers \Engelsystem\Helpers\Schedule\Room::getName
     * @covers \Engelsystem\Helpers\Schedule\Room::getEvents
     * @covers \Engelsystem\Helpers\Schedule\Room::setEvents
     */
    public function testCreate(): void
    {
        $room = new Room('Test');
        $this->assertEquals('Test', $room->getName());
        $this->assertEquals([], $room->getEvents());

        $events = [$this->createMock(Event::class), $this->createMock(Event::class)];
        $events2 = [$this->createMock(Event::class)];
        $room = new Room('Test2', $events);
        $this->assertEquals($events, $room->getEvents());

        $room->setEvents($events2);
        $this->assertEquals($events2, $room->getEvents());
    }
}
