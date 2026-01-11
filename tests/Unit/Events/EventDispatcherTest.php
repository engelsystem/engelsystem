<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\EventDispatcher;
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

        $event->fire('foo');
        $event->fire('bar', 'Test!');

        $this->assertEquals(
            ['foo' => ['count' => 2, ['foo'], ['foo']], 'bar' => ['count' => 1, ['bar', 'Test!']]],
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

        $this->assertEquals([], $response);
    }

    public function testDispatchObject(): void
    {
        $event = new EventDispatcher();
        $event->listen(static::class, [$this, 'eventHandler']);
        $event->fire($this);

        $this->assertEquals([static::class => ['count' => 1, [static::class, $this]]], $this->firedEvents);
    }

    public function testDispatchHalt(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', [$this, 'returnNull']);
        $event->listen('test', [$this, 'returnData']);
        $event->listen('test', [$this, 'eventHandler']);
        $response = $event->dispatch('test', [], true);

        $this->assertEquals(['example' => 'data'], $response);
        $this->assertEquals([], $this->firedEvents);

        $event = new EventDispatcher();
        $response = $event->dispatch('test', [], true);
        $this->assertNull($response);
    }

    public function testDispatchStopPropagation(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', [$this, 'returnNull']);
        $event->listen('test', [$this, 'returnFalse']);
        $event->listen('test', [$this, 'eventHandler']);
        $response = $event->dispatch('test');

        $this->assertEquals([null], $response);
        $this->assertEquals([], $this->firedEvents);
    }

    public function testDispatchFallbackHandleMethod(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', TestEventDispatcher::class);
        $response = $event->dispatch('test', [], true);

        $this->assertEquals(['default' => 'handler'], $response);
    }

    public function eventHandler(string $event): void
    {
        if (!isset($this->firedEvents[$event])) {
            $this->firedEvents[$event] = ['count' => 0];
        }

        $this->firedEvents[$event]['count']++;
        $this->firedEvents[$event][] = func_get_args();
    }

    public function returnNull(): null
    {
        return null;
    }

    public function returnFalse(): bool
    {
        return false;
    }

    public function returnData(): array
    {
        return ['example' => 'data'];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->firedEvents = [];
    }
}
