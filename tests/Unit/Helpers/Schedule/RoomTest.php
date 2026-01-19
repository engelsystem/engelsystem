<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Room::class, '__construct')]
#[CoversMethod(Room::class, 'getName')]
#[CoversMethod(Room::class, 'getEvents')]
#[CoversMethod(Room::class, 'getGuid')]
#[CoversMethod(Room::class, 'setEvents')]
class RoomTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $room = new Room('Test');
        $this->assertEquals('Test', $room->getName());
        $this->assertEquals([], $room->getEvents());
        $this->assertNull($room->getGuid());
    }
    public function testCreate(): void
    {
        $uuid = Uuid::uuid();
        $events = [$this->createStub(Event::class), $this->createStub(Event::class)];
        $events2 = [$this->createStub(Event::class)];
        $room = new Room('Test2', $uuid, $events);
        $this->assertEquals($events, $room->getEvents());
        $this->assertEquals($uuid, $room->getGuid());

        $room->setEvents($events2);
        $this->assertEquals($events2, $room->getEvents());
    }
}
