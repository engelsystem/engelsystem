<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\Day;
use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Schedule\XmlParser;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Arr;

class XmlParserTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::load
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseXml
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseEvents
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getFirstXpathContent
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getListFromSequence
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getSchedule
     */
    public function testLoad(): void
    {
        $parser = new XmlParser();

        // Invalid XML
        $this->assertFalse($parser->load('foo'));
        // Invalid schedule
        $this->assertFalse($parser->load(file_get_contents(__DIR__ . '/Assets/schedule-invalid.html')));

        // Minimal import
        $this->assertTrue($parser->load(file_get_contents(__DIR__ . '/Assets/schedule-minimal.xml')));
        // Basic import
        $this->assertTrue($parser->load(file_get_contents(__DIR__ . '/Assets/schedule-basic.xml')));
        // Extended import
        $this->assertTrue($parser->load(file_get_contents(__DIR__ . '/Assets/schedule-extended.xml')));

        $schedule = $parser->getSchedule();
        $this->assertEquals('Some version string', $schedule->getVersion());
        $this->assertEquals('Test Event', $schedule->getConference()->getTitle());

        /** @var Room $room */
        $room = Arr::first($schedule->getRooms());
        $this->assertEquals('Rooming', $room->getName());

        /** @var Day $day */
        $day = Arr::first($schedule->getDay());
        $this->assertEquals('2042-01-01', $day->getDate());
        $this->assertEquals(1, $day->getIndex());

        /** @var Room $room */
        $room = Arr::first($day->getRoom());
        /** @var Event $event */
        $event = Arr::first($room->getEvent());

        $this->assertEquals('Foo Bar Test', $event->getTitle());
        $this->assertEquals('WTFPL', $event->getRecording());
        $this->assertEquals('de', $event->getLanguage());
        $this->assertEquals('12:30', $event->getStart());
        $this->assertEquals([1234 => 'Some Person'], $event->getPersons());
        $this->assertEquals('https://foo.bar/baz/schedule/ipsum/recording.mp4', $event->getVideoDownloadUrl());
    }
}
