<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ConferenceTrack;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ConferenceTrack::class, '__construct')]
#[CoversMethod(ConferenceTrack::class, 'getName')]
#[CoversMethod(ConferenceTrack::class, 'getColor')]
#[CoversMethod(ConferenceTrack::class, 'getSlug')]
class ConferenceTrackTest extends TestCase
{
    public function testCreateDefaults(): void
    {
        $conferenceColor = new ConferenceTrack('Tracking');

        $this->assertEquals('Tracking', $conferenceColor->getName());
        $this->assertNull($conferenceColor->getColor());
        $this->assertNull($conferenceColor->getSlug());
    }

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
