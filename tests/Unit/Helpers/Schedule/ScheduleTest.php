<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Carbon\Carbon;
use Engelsystem\Helpers\Schedule\Conference;
use Engelsystem\Helpers\Schedule\Day;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Schedule\Schedule;
use Engelsystem\Helpers\Schedule\ScheduleGenerator;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

class ScheduleTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\Schedule\Schedule::__construct
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getVersion
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getConference
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getDays
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getGenerator
     */
    public function testCreate(): void
    {
        $conference = new Conference('Foo Bar', 'FooB');
        $days = [$this->createMock(Day::class)];
        $schedule = new Schedule('Foo\'ing stuff 1.0', $conference, $days);

        $this->assertEquals('Foo\'ing stuff 1.0', $schedule->getVersion());
        $this->assertEquals($conference, $schedule->getConference());
        $this->assertEquals($days, $schedule->getDays());
        $this->assertNull($schedule->getGenerator());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getAllRooms
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getRooms
     */
    public function testGetRooms(): void
    {
        $conference = new Conference('Test', 'T');
        $room1 = new Room('Test 1');
        $room2 = new Room('Test 2');
        $room3 = new Room('Test 3');
        $room4 = new Room('Test 2');
        $days = [
            new Day(
                '2042-01-01',
                new Carbon('2042-01-01T00:00:00+00:00'),
                new Carbon('2042-01-01T23:59:00+00:00'),
                1,
                [$room1, $room2],
            ),
            new Day(
                '2042-01-02',
                new Carbon('2042-02-02T00:00:00+00:00'),
                new Carbon('2042-02-02T23:59:00+00:00'),
                2,
                [$room4, $room3],
            ),
        ];
        $schedule = new Schedule('Lorem 1.3.3.7', $conference, $days);

        $this->assertEquals(['Test 1' => $room1, 'Test 2' => $room4, 'Test 3' => $room3], $schedule->getRooms());
        $this->assertEquals([$room1, $room2, $room4, $room3], $schedule->getAllRooms());
        $this->assertTrue($room4 === $schedule->getRooms()['Test 2']); // Rooms should use last room occurrence

        $schedule = new Schedule('Lorem 1.3.3.0', $conference, []);
        $this->assertEquals([], $schedule->getRooms());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getStartDateTime
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getEndDateTime
     */
    public function testGetDateTimes(): void
    {
        $conference = new Conference('Some Conference', 'SC');
        $days = [
            new Day(
                '2042-01-02',
                new Carbon('2042-01-02T00:00:00+00:00'),
                new Carbon('2042-01-02T23:59:00+00:00'),
                2
            ),
            new Day(
                '2042-01-01',
                new Carbon('2042-01-01T00:00:00+00:00'),
                new Carbon('2042-01-01T23:59:00+00:00'),
                1
            ),
            new Day(
                '2042-01-04',
                new Carbon('2042-01-04T00:00:00+00:00'),
                new Carbon('2042-01-04T23:59:00+00:00'),
                3
            ),
        ];
        $schedule = new Schedule('Ipsum tester', $conference, $days);

        $this->assertEquals('2042-01-01T00:00:00+00:00', $schedule->getStartDateTime()->format(Carbon::RFC3339));
        $this->assertEquals('2042-01-04T23:59:00+00:00', $schedule->getEndDateTime()->format(Carbon::RFC3339));

        $schedule = new Schedule('Ipsum old', $conference, []);
        $this->assertNull($schedule->getStartDateTime());
        $this->assertNull($schedule->getEndDateTime());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\Schedule::__construct
     * @covers \Engelsystem\Helpers\Schedule\Schedule::getGenerator
     */
    public function testGetGenerator(): void
    {
        $conference = new Conference('Foo Bar', 'FooB');
        $generator = new ScheduleGenerator('test', '1337');
        $schedule = new Schedule('1.0', $conference, [], $generator);

        $this->assertEquals($generator, $schedule->getGenerator());
    }

    /**
     * Prepare test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
    }
}
