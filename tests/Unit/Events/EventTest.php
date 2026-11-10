<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\Event;
use Engelsystem\Events\NullEvent;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Event::class, 'getName')]
class EventTest extends TestCase
{
    public function testGetNameNotSet(): void
    {
        $event = new Event();

        $this->assertEquals(Event::class, $event->getName());
    }

    public function testGetNameFromName(): void
    {
        $event = new NullEvent('foo');

        $this->assertEquals('foo', $event->getName());
    }
}
