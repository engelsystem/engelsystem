<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ConferenceTrack;
use Engelsystem\Test\Unit\TestCase;

class ConferenceTrackTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::__construct
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getName
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getColor
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getSlug
     */
    public function testCreateDefaults(): void
    {
        $conferenceColor = new ConferenceTrack('Tracking');

        $this->assertEquals('Tracking', $conferenceColor->getName());
        $this->assertNull($conferenceColor->getColor());
        $this->assertNull($conferenceColor->getSlug());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::__construct
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getName
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getColor
     * @covers \Engelsystem\Helpers\Schedule\ConferenceTrack::getSlug
     */
    public function testCreate(): void
    {
        $conferenceColor = new ConferenceTrack(
            'Testing',
            '#abcdef',
            'testing'
        );

        $this->assertEquals('Testing', $conferenceColor->getName());
        $this->assertEquals('#abcdef', $conferenceColor->getColor());
        $this->assertEquals('testing', $conferenceColor->getSlug());
    }
}
