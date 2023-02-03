<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\Conference;
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
     */
    public function testCreate(): void
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

        $conference = new Conference(
            'Doing stuff',
            'DS',
            '2042-01-01',
            '2042-01-10',
            10,
            '00:10',
            'https://foo.bar/schedule'
        );
        $this->assertEquals('2042-01-01', $conference->getStart());
        $this->assertEquals('2042-01-10', $conference->getEnd());
        $this->assertEquals(10, $conference->getDays());
        $this->assertEquals('00:10', $conference->getTimeslotDuration());
        $this->assertEquals(60 * 10, $conference->getTimeslotDurationSeconds());
        $this->assertEquals('https://foo.bar/schedule', $conference->getBaseUrl());
    }
}
