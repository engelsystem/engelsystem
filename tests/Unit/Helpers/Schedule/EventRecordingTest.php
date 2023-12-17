<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\EventRecording;
use Engelsystem\Test\Unit\TestCase;

class EventRecordingTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::__construct
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getLicense
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::isOptOut
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getUrl
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getLink
     */
    public function testCreateDefaults(): void
    {
        $eventRecording = new EventRecording(
            'WTFPL',
            true
        );

        $this->assertEquals('WTFPL', $eventRecording->getLicense());
        $this->assertTrue($eventRecording->isOptOut());
        $this->assertNull($eventRecording->getUrl());
        $this->assertNull($eventRecording->getLink());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::__construct
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getLicense
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::isOptOut
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getUrl
     * @covers \Engelsystem\Helpers\Schedule\EventRecording::getLink
     */
    public function testCreate(): void
    {
        $eventRecording = new EventRecording(
            'BeerWare',
            false,
            'https://example.com/recording',
            'https://exampple.com/license'
        );

        $this->assertEquals('BeerWare', $eventRecording->getLicense());
        $this->assertFalse($eventRecording->isOptOut());
        $this->assertEquals('https://example.com/recording', $eventRecording->getUrl());
        $this->assertEquals('https://exampple.com/license', $eventRecording->getLink());
    }
}
