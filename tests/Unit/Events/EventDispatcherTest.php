<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\EventDispatcher;
use Engelsystem\Test\Unit\TestCase;

class EventDispatcherTest extends TestCase
{
    /** @var array */
    protected array $firedEvents = [];

    /**
     * @covers \Engelsystem\Events\EventDispatcher::listen
     * @covers \Engelsystem\Events\EventDispatcher::fire
     */
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

    /**
     * @covers \Engelsystem\Events\EventDispatcher::forget
     */
    public function testForget(): void
    {
        $event = new EventDispatcher();
        $event->forget('not-existing-event');

        $event->listen('test', [$this, 'eventHandler']);
        $event->forget('test');

        $event->fire('test');

        $this->assertEquals([], $this->firedEvents);
    }

    /**
     * @covers \Engelsystem\Events\EventDispatcher::dispatch
     */
    public function testDispatchNotExistingEvent(): void
    {
        $event = new EventDispatcher();
        $response = $event->fire('not-existing-event');

        $this->assertEquals([], $response);
    }

    /**
     * @covers \Engelsystem\Events\EventDispatcher::dispatch
     */
    public function testDispatchObject(): void
    {
        $event = new EventDispatcher();
        $event->listen(static::class, [$this, 'eventHandler']);
        $event->fire($this);

        $this->assertEquals([static::class => ['count' => 1, [static::class, $this]]], $this->firedEvents);
    }

    /**
     * @covers \Engelsystem\Events\EventDispatcher::dispatch
     */
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

    /**
     * @covers \Engelsystem\Events\EventDispatcher::dispatch
     */
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

    /**
     * @covers \Engelsystem\Events\EventDispatcher::dispatch
     */
    public function testDispatchFallbackHandleMethod(): void
    {
        $event = new EventDispatcher();
        $event->listen('test', EventDispatcherTest::class);
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

    /**
     * @return null
     */
    public function returnNull()
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

    public function handle(): array
    {
        return ['default' => 'handler'];
    }
}
