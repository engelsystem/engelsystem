<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\DataEvent;
use Engelsystem\Events\Event;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\NullEvent;
use Engelsystem\Events\StoppableEvent;
use Engelsystem\Test\Unit\Events\Stub\TestEventDispatcher;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(EventDispatcher::class, 'listen')]
#[CoversMethod(EventDispatcher::class, 'fire')]
#[CoversMethod(EventDispatcher::class, 'forget')]
#[CoversMethod(EventDispatcher::class, 'dispatch')]
class EventDispatcherTest extends TestCase
{
    protected array $firedEvents = [];

    public function testListen(): void
    {
        $event = new EventDispatcher();
        $event->listen('foo', [$this, 'eventHandler']);
        $event->listen(['foo', 'bar'], [$this, 'eventHandler']);
        $event->listen('foo.*', [$this, 'eventHandler']);
        $event->listen('test.*', [$this, 'eventHandler']);

        $event->fire('foo');
        $event->fire(new DataEvent('bar', ['Test!']));
        $event->fire('test.a');
        $event->fire('test.b');

        $this->assertEquals(
            [
                'foo' => ['count' => 2, new NullEvent('foo'), new NullEvent('foo')],
                'bar' => ['count' => 1, new DataEvent('bar', ['Test!'])],
                'test.a' => ['count' => 1, new NullEvent('test.a')],
                'test.b' => ['count' => 1, new NullEvent('test.b')],
            ],
            $this->firedEvents
        );
    }

    public function testForget(): void
    {
        $event = new EventDispatcher();
        $event->forget('not-existing-event');

        $event->listen('test', [$this, 'eventHandler']);
        $event->forget('test');

        $event->fire('test');

        $this->assertEquals([], $this->firedEvents);
    }

    public function testDispatchNotExistingEvent(): void
    {
        $event = new EventDispatcher();
        $response = $event->fire('not-existing-event');

        $this->assertEquals(new NullEvent('not-existing-event'), $response);
    }

    public function testDispatchObject(): void
    {
        $event = new EventDispatcher();
        $event->listen(static::class, [$this, 'eventHandler']);
        $event->fire($this);

        $this->assertEquals([static::class => ['count' => 1, $this]], $this->firedEvents);
    }

    public function testDispatchStopPropagation(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', [$this, 'stopPropagate']);
        $event->listen('test', [$this, 'eventHandler']);
        $response = $event->dispatch('test');

        $stoppedEvent = new NullEvent('test');
        $stoppedEvent->stopPropagation();
        $this->assertEquals($stoppedEvent, $response);
        $this->assertEquals([], $this->firedEvents);
    }

    public function testDispatchFallbackHandleMethod(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', TestEventDispatcher::class);
        $response = $event->dispatch('test');

        $this->assertEquals(new NullEvent('test'), $response);
        $this->assertTrue(TestEventDispatcher::$handled);
        TestEventDispatcher::$handled = false;
    }

    public function eventHandler(object $event): void
    {
        $eventName = $event instanceof Event ? $event->getName() : get_class($event);
        if (!isset($this->firedEvents[$eventName])) {
            $this->firedEvents[$eventName] = ['count' => 0];
        }

        $this->firedEvents[$eventName]['count']++;
        $this->firedEvents[$eventName][] = $event;
    }

    public function stopPropagate(StoppableEvent $event): void
    {
        $event->stopPropagation();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->firedEvents = [];
    }
}
