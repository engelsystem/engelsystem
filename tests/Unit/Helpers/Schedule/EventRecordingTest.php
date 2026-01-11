<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\EventRecording;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(EventRecording::class, '__construct')]
#[CoversMethod(EventRecording::class, 'getLicense')]
#[CoversMethod(EventRecording::class, 'isOptOut')]
#[CoversMethod(EventRecording::class, 'getUrl')]
#[CoversMethod(EventRecording::class, 'getLink')]
class EventRecordingTest extends TestCase
{
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
