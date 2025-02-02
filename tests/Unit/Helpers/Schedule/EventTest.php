<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Carbon\Carbon;
use Engelsystem\Helpers\Schedule\ConferenceTrack;
use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\EventRecording;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Test\Unit\TestCase;

class EventTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\Event::__construct
     * @covers \Engelsystem\Helpers\Schedule\Event::getGuid
     * @covers \Engelsystem\Helpers\Schedule\Event::getId
     * @covers \Engelsystem\Helpers\Schedule\Event::getRoom
     * @covers \Engelsystem\Helpers\Schedule\Event::getTitle
     * @covers \Engelsystem\Helpers\Schedule\Event::getSubtitle
     * @covers \Engelsystem\Helpers\Schedule\Event::getType
     * @covers \Engelsystem\Helpers\Schedule\Event::getDate
     * @covers \Engelsystem\Helpers\Schedule\Event::getStart
     * @covers \Engelsystem\Helpers\Schedule\Event::getDuration
     * @covers \Engelsystem\Helpers\Schedule\Event::getDurationSeconds
     * @covers \Engelsystem\Helpers\Schedule\Event::getAbstract
     * @covers \Engelsystem\Helpers\Schedule\Event::getSlug
     * @covers \Engelsystem\Helpers\Schedule\Event::getTrack
     * @covers \Engelsystem\Helpers\Schedule\Event::getLogo
     * @covers \Engelsystem\Helpers\Schedule\Event::getPersons
     * @covers \Engelsystem\Helpers\Schedule\Event::getLanguage
     * @covers \Engelsystem\Helpers\Schedule\Event::getDescription
     * @covers \Engelsystem\Helpers\Schedule\Event::getRecording
     * @covers \Engelsystem\Helpers\Schedule\Event::getLinks
     * @covers \Engelsystem\Helpers\Schedule\Event::getAttachments
     * @covers \Engelsystem\Helpers\Schedule\Event::getUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getFeedbackUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getOriginUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getVideoDownloadUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getEndDate
     */
    public function testCreateDefault(): void
    {
        $room = new Room('Foo');
        $date = new Carbon('2020-12-28T19:30:00+00:00');
        $uuid = Uuid::uuid();
        $event = new Event(
            $uuid,
            1,
            $room,
            'Some stuff',
            'sub stuff',
            'Talk',
            $date,
            '19:30:00',
            '00:50',
            'Doing stuff is hard, plz try again',
            '1-some-stuff',
            new ConferenceTrack('Security'),
        );

        $this->assertEquals($uuid, $event->getGuid());
        $this->assertEquals(1, $event->getId());
        $this->assertEquals($room, $event->getRoom());
        $this->assertEquals('Some stuff', $event->getTitle());
        $this->assertEquals('sub stuff', $event->getSubtitle());
        $this->assertEquals('Talk', $event->getType());
        $this->assertEquals($date, $event->getDate());
        $this->assertEquals('19:30:00', $event->getStart());
        $this->assertEquals('00:50', $event->getDuration());
        $this->assertEquals('Doing stuff is hard, plz try again', $event->getAbstract());
        $this->assertEquals('1-some-stuff', $event->getSlug());
        $this->assertEquals('Security', $event->getTrack()->getName());
        $this->assertNull($event->getLogo());
        $this->assertEquals([], $event->getPersons());
        $this->assertNull($event->getLanguage());
        $this->assertNull($event->getDescription());
        $this->assertNull($event->getRecording());
        $this->assertEquals([], $event->getLinks());
        $this->assertEquals([], $event->getAttachments());
        $this->assertNull($event->getUrl());
        $this->assertNull($event->getVideoDownloadUrl());
        $this->assertNull($event->getFeedbackUrl());
        $this->assertNull($event->getOriginUrl());
        $this->assertEquals('2020-12-28T20:20:00+00:00', $event->getEndDate()->format(Carbon::RFC3339));
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\Event::__construct
     * @covers \Engelsystem\Helpers\Schedule\Event::getGuid
     * @covers \Engelsystem\Helpers\Schedule\Event::getId
     * @covers \Engelsystem\Helpers\Schedule\Event::getRoom
     * @covers \Engelsystem\Helpers\Schedule\Event::getTitle
     * @covers \Engelsystem\Helpers\Schedule\Event::setTitle
     * @covers \Engelsystem\Helpers\Schedule\Event::getSubtitle
     * @covers \Engelsystem\Helpers\Schedule\Event::getType
     * @covers \Engelsystem\Helpers\Schedule\Event::getDate
     * @covers \Engelsystem\Helpers\Schedule\Event::getStart
     * @covers \Engelsystem\Helpers\Schedule\Event::getDuration
     * @covers \Engelsystem\Helpers\Schedule\Event::getDurationSeconds
     * @covers \Engelsystem\Helpers\Schedule\Event::getAbstract
     * @covers \Engelsystem\Helpers\Schedule\Event::getSlug
     * @covers \Engelsystem\Helpers\Schedule\Event::getTrack
     * @covers \Engelsystem\Helpers\Schedule\Event::getLogo
     * @covers \Engelsystem\Helpers\Schedule\Event::getPersons
     * @covers \Engelsystem\Helpers\Schedule\Event::getLanguage
     * @covers \Engelsystem\Helpers\Schedule\Event::getDescription
     * @covers \Engelsystem\Helpers\Schedule\Event::getRecording
     * @covers \Engelsystem\Helpers\Schedule\Event::getLinks
     * @covers \Engelsystem\Helpers\Schedule\Event::getAttachments
     * @covers \Engelsystem\Helpers\Schedule\Event::getUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getFeedbackUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getOriginUrl
     * @covers \Engelsystem\Helpers\Schedule\Event::getVideoDownloadUrl
     */
    public function testCreate(): void
    {
        $persons = [1337 => 'Some Person'];
        $links = ['https://foo.bar' => 'Foo Bar'];
        $attachments = ['/files/foo.pdf' => 'Suspicious PDF'];
        $event = new Event(
            Uuid::uuid(),
            2,
            new Room('Bar'),
            'Lorem',
            'Ipsum',
            'Workshop',
            new Carbon('2021-01-01T00:00:00+00:00'),
            '00:00:00',
            '00:30',
            'Lorem ipsum dolor sit amet',
            '2-lorem',
            new ConferenceTrack('DevOps'),
            '/foo/bar.png',
            $persons,
            'de',
            'Foo bar is awesome! & That\'s why...',
            new EventRecording('CC BY SA', false),
            $links,
            $attachments,
            'https://foo.bar/2-lorem',
            'https://videos.orem.ipsum/2-lorem.mp4',
            'https://videos.orem.ipsum/2-lorem/feedback',
            'https://some.example/2-lorem/',
        );

        $this->assertEquals('/foo/bar.png', $event->getLogo());
        $this->assertEquals($persons, $event->getPersons());
        $this->assertEquals('de', $event->getLanguage());
        $this->assertEquals('Foo bar is awesome! & That\'s why...', $event->getDescription());
        $this->assertNotNull($event->getRecording());
        $this->assertEquals('CC BY SA', $event->getRecording()->getLicense());
        $this->assertEquals($links, $event->getLinks());
        $this->assertEquals($attachments, $event->getAttachments());
        $this->assertEquals('https://foo.bar/2-lorem', $event->getUrl());
        $this->assertEquals('https://videos.orem.ipsum/2-lorem.mp4', $event->getVideoDownloadUrl());
        $this->assertEquals('https://videos.orem.ipsum/2-lorem/feedback', $event->getFeedbackUrl());
        $this->assertEquals('https://some.example/2-lorem/', $event->getOriginUrl());

        $event->setTitle('Event title');
        $this->assertEquals('Event title', $event->getTitle());
    }
}
