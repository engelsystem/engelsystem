<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\StoppableEvent;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(StoppableEvent::class, 'stopPropagation')]
#[CoversMethod(StoppableEvent::class, 'isPropagationStopped')]
class StoppableEventTest extends TestCase
{
    public function testPropagation(): void
    {
        $event = new StoppableEvent();
        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
