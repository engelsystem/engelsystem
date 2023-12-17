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
     * @covers \Engelsystem\Helpers\Schedule\Room::getGuid
     */
    public function testCreateDefault(): void
    {
        $room = new Room('Test');
        $this->assertEquals('Test', $room->getName());
        $this->assertEquals([], $room->getEvents());
        $this->assertNull($room->getGuid());
    }
    /**
     * @covers \Engelsystem\Helpers\Schedule\Room::__construct
     * @covers \Engelsystem\Helpers\Schedule\Room::getName
     * @covers \Engelsystem\Helpers\Schedule\Room::getEvents
     * @covers \Engelsystem\Helpers\Schedule\Room::setEvents
     * @covers \Engelsystem\Helpers\Schedule\Room::getGuid
     */
    public function testCreate(): void
    {
        $uuid = Uuid::uuid();
        $events = [$this->createMock(Event::class), $this->createMock(Event::class)];
        $events2 = [$this->createMock(Event::class)];
        $room = new Room('Test2', $uuid, $events);
        $this->assertEquals($events, $room->getEvents());
        $this->assertEquals($uuid, $room->getGuid());

        $room->setEvents($events2);
        $this->assertEquals($events2, $room->getEvents());
    }
}
