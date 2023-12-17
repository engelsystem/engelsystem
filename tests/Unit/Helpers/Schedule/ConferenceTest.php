<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\Conference;
use Engelsystem\Helpers\Schedule\ConferenceColor;
use Engelsystem\Helpers\Schedule\ConferenceTrack;
use Engelsystem\Test\Unit\TestCase;

class ConferenceTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\Conference::__construct
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTitle
     * @covers \Engelsystem\Helpers\Schedule\Conference::getAcronym
     * @covers \Engelsystem\Helpers\Schedule\Conference::getStart
     * @covers \Engelsystem\Helpers\Schedule\Conference::getEnd
     * @covers \Engelsystem\Helpers\Schedule\Conference::getDays
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeslotDuration
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeslotDurationSeconds
     * @covers \Engelsystem\Helpers\Schedule\Conference::getBaseUrl
     * @covers \Engelsystem\Helpers\Schedule\Conference::getLogo
     * @covers \Engelsystem\Helpers\Schedule\Conference::getUrl
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeZoneName
     * @covers \Engelsystem\Helpers\Schedule\Conference::getColor
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTracks
     */
    public function testCreateDefault(): void
    {
        $conference = new Conference('Doing stuff', 'DS');
        $this->assertEquals('Doing stuff', $conference->getTitle());
        $this->assertEquals('DS', $conference->getAcronym());
        $this->assertNull($conference->getStart());
        $this->assertNull($conference->getEnd());
        $this->assertNull($conference->getDays());
        $this->assertNull($conference->getTimeslotDuration());
        $this->assertNull($conference->getTimeslotDurationSeconds());
        $this->assertNull($conference->getBaseUrl());
        $this->assertNull($conference->getLogo());
        $this->assertNull($conference->getUrl());
        $this->assertNull($conference->getTimeZoneName());
        $this->assertNull($conference->getColor());
        $this->assertEmpty($conference->getTracks());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\Conference::__construct
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTitle
     * @covers \Engelsystem\Helpers\Schedule\Conference::getAcronym
     * @covers \Engelsystem\Helpers\Schedule\Conference::getStart
     * @covers \Engelsystem\Helpers\Schedule\Conference::getEnd
     * @covers \Engelsystem\Helpers\Schedule\Conference::getDays
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeslotDuration
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeslotDurationSeconds
     * @covers \Engelsystem\Helpers\Schedule\Conference::getBaseUrl
     * @covers \Engelsystem\Helpers\Schedule\Conference::getLogo
     * @covers \Engelsystem\Helpers\Schedule\Conference::getUrl
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTimeZoneName
     * @covers \Engelsystem\Helpers\Schedule\Conference::getColor
     * @covers \Engelsystem\Helpers\Schedule\Conference::getTracks
     */
    public function testCreate(): void
    {
        $conference = new Conference(
            'Doing stuff',
            'DS',
            '2042-01-01',
            '2042-01-10',
            10,
            '00:10',
            'https://foo.bar/schedule',
            'https://foo.bar/logo.png',
            'https://foo.bar',
            'Europe/Berlin',
            new ConferenceColor('#ffffff'),
            [new ConferenceTrack('Test')]
        );
        $this->assertEquals('Doing stuff', $conference->getTitle());
        $this->assertEquals('DS', $conference->getAcronym());
        $this->assertEquals('2042-01-01', $conference->getStart());
        $this->assertEquals('2042-01-10', $conference->getEnd());
        $this->assertEquals(10, $conference->getDays());
        $this->assertEquals('00:10', $conference->getTimeslotDuration());
        $this->assertEquals(60 * 10, $conference->getTimeslotDurationSeconds());
        $this->assertEquals('https://foo.bar/schedule', $conference->getBaseUrl());
        $this->assertEquals('https://foo.bar/logo.png', $conference->getLogo());
        $this->assertEquals('https://foo.bar', $conference->getUrl());
        $this->assertEquals('Europe/Berlin', $conference->getTimeZoneName());
        $this->assertNotNull($conference->getColor());
        $this->assertEquals('#ffffff', $conference->getColor()->getPrimary());
        $this->assertNotNull($conference->getTracks());
        $this->assertEquals('Test', $conference->getTracks()[0]->getName());
    }
}
