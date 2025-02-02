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
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseGenerator
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseConferenceColor
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseTracks
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseEvents
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::parseRecording
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getFirstXpathContent
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getListFromSequence
     * @covers \Engelsystem\Helpers\Schedule\XmlParser::getSchedule
     */
    public function testLoad(): void
    {
        $dateTimeFormat = 'Y-m-d\TH:i:sP';
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

        $generator = $schedule->getGenerator();
        $this->assertNotNull($generator);
        $this->assertEquals('Engelsystem', $generator->getName());
        $this->assertEquals('1.2.3', $generator->getVersion());

        $this->assertEquals('some-version-string', $schedule->getVersion());

        $conference = $schedule->getConference();
        $this->assertEquals('Test Event', $conference->getTitle());
        $this->assertEquals('test-3', $conference->getAcronym());
        $this->assertEquals('2042-01-01T01:00:00+02:00', $conference->getStart());
        $this->assertEquals('2042-01-01T22:59:00+02:00', $conference->getEnd());
        $this->assertEquals(1, $conference->getDays());
        $this->assertEquals('00:15', $conference->getTimeslotDuration());
        $this->assertEquals('https://foo.bar/baz/schedule/', $conference->getBaseUrl());
        $this->assertEquals('https://foo.bar/baz.png', $conference->getLogo());
        $this->assertEquals('https://foo.bar/', $conference->getUrl());
        $this->assertEquals('Europe/Berlin', $conference->getTimeZoneName());
        $color = $conference->getColor();
        $this->assertNotNull($color);
        $this->assertEquals('#abcdef', $color->getPrimary());
        $this->assertEquals('#aabbcc', $color->getBackground());
        $this->assertEquals(['customColor' => '#s011e7'], $color->getOthers());
        $tracks = $conference->getTracks();
        $this->assertNotNull($tracks);
        $this->assertEquals('Testing', $tracks[0]->getName());
        $this->assertEquals('#dead55', $tracks[0]->getColor());
        $this->assertEquals('testing', $tracks[0]->getSlug());

        /** @var Day $day */
        $day = Arr::first($schedule->getDays());
        $this->assertEquals('2042-01-01', $day->getDate());
        $this->assertEquals(1, $day->getIndex());
        $this->assertEquals('2042-01-01T01:00:00+02:00', $day->getStart()->format($dateTimeFormat));
        $this->assertEquals('2042-01-01T22:59:00+02:00', $day->getEnd()->format($dateTimeFormat));

        /** @var Room $room */
        $room = Arr::first($schedule->getRooms());
        $this->assertEquals('Rooming', $room->getName());
        $this->assertEquals('bf5f1132-82bd-4da2-bbe0-1abbf8daf4ab', $room->getGuid());

        /** @var Room $room */
        $room = Arr::first($day->getRooms());
        /** @var Event $event */
        $event = Arr::first($room->getEvents());

        $this->assertEquals('e427cfa9-9ba1-4b14-a99f-bce83ffe5a1c', $event->getGuid());
        $this->assertEquals('1337', $event->getId());
        $this->assertEquals('2042-01-01T12:30:00+02:00', $event->getDate()->format($dateTimeFormat));
        $this->assertEquals('Foo Bar Test', $event->getTitle());
        $this->assertEquals('Some sub', $event->getSubtitle());
        $this->assertEquals('12:30', $event->getStart());
        $this->assertEquals('00:30', $event->getDuration());
        $this->assertEquals($room, $event->getRoom());
        $this->assertEquals('some-3-test', $event->getSlug());
        $recording = $event->getRecording();
        $this->assertNotNull($recording);
        $this->assertEquals('WTFPL', $recording->getLicense());
        $this->assertFalse($recording->isOptOut());
        $this->assertEquals('https://recorder.test/some-3-test/recorded', $recording->getUrl());
        $this->assertEquals('https://recorder.test/some-3-test', $recording->getLink());
        $this->assertEquals('https://foo.bar/baz/schedule/ipsum/recording.mp4', $event->getVideoDownloadUrl());
        $this->assertEquals('Testing', $event->getTrack()->getName());
        $this->assertEquals('Talk', $event->getType());
        $this->assertEquals('de', $event->getLanguage());
        $this->assertEquals('Foo bar is da best', $event->getAbstract());
        $this->assertEquals('Any describing stuff?', $event->getDescription());
        $this->assertEquals('https://foo.bar/baz/schedule/ipsum', $event->getUrl());
        $this->assertEquals('https://foo.bar/baz/schedule/ipsum#feedback', $event->getFeedbackUrl());
        $this->assertEquals('https://some.example/event/ipsum', $event->getOriginUrl());
        $this->assertEquals('https://lorem.ipsum/foo/bar.png', $event->getLogo());
        $this->assertEquals([1234 => 'Some Person', 1337 => 'Another Person'], $event->getPersons());
        $this->assertEquals([
            'https://foo.bar' => 'Some Foo Bar',
            'https://example.com' => 'Another example',
        ], $event->getLinks());
        $this->assertEquals([
            'https://foo.bar/stuff.pdf' => 'A PDF File',
            'https://foo.bar/something.png' => 'An image',
        ], $event->getAttachments());
    }
}
