<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\LaravelEvent;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(LaravelEvent::class, '__construct')]
#[CoversMethod(LaravelEvent::class, 'stopPropagation')]
class LaravelEventTest extends TestCase
{
    public function testCreation(): void
    {
        $event = new LaravelEvent('some-name', ['data'], true);

        $this->assertEquals('some-name', $event->getName());
        $this->assertEquals(['data'], $event->payload);
        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testCreationStoppedPropagation(): void
    {
        $event = new LaravelEvent('event', null, false);

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertFalse($event->isPropagationStopped());
    }
}
