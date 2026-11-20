<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\NullEvent;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NullEvent::class, '__construct')]
class NullEventTest extends TestCase
{
    public function testCreation(): void
    {
        $event = new NullEvent();
        $this->assertEquals(NullEvent::class, $event->getName());
    }
    public function testCreationNamed(): void
    {
        $event = new NullEvent('named.event');
        $this->assertEquals('named.event', $event->getName());
    }
}
