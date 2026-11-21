<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\LaravelEvent;
use Engelsystem\Events\LaravelEventDispatcher;
use Engelsystem\Events\NullEvent;
use Engelsystem\Test\Unit\TestCase;
use ErrorException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(LaravelEventDispatcher::class, '__construct')]
#[CoversMethod(LaravelEventDispatcher::class, 'listen')]
#[CoversMethod(LaravelEventDispatcher::class, 'hasListeners')]
#[CoversMethod(LaravelEventDispatcher::class, 'subscribe')]
#[CoversMethod(LaravelEventDispatcher::class, 'until')]
#[CoversMethod(LaravelEventDispatcher::class, 'dispatch')]
#[CoversMethod(LaravelEventDispatcher::class, 'push')]
#[CoversMethod(LaravelEventDispatcher::class, 'flush')]
#[CoversMethod(LaravelEventDispatcher::class, 'forget')]
#[CoversMethod(LaravelEventDispatcher::class, 'forgetPushed')]
#[AllowMockObjectsWithoutExpectations]
class LaravelEventDispatcherTest extends TestCase
{
    protected EventDispatcher&MockObject $dispatcher;

    public function testListen(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);
        $cb = fn() => null;

        $this->setExpects($this->dispatcher, 'listen', ['foo.bar', $cb]);

        $led->listen('foo.bar', $cb);
    }

    public function testHasListeners(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->expectException(ErrorException::class);
        $led->hasListeners('foo.bar');
    }

    public function testSubscribe(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);
        $cb = fn() => null;

        $this->expectException(ErrorException::class);
        $led->subscribe($cb);
    }

    public function testUntil(): void
    {
        $event = new LaravelEvent('foo.bar', ['blubber'], true);

        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->setExpects($this->dispatcher, 'dispatch', [$event], $event);

        $return = $led->until('foo.bar', ['blubber']);
        $this->assertNull($return);
    }

    public function testDispatchHaltable(): void
    {
        $event = new LaravelEvent('foo.bar', ['blubber'], false);

        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->setExpects($this->dispatcher, 'dispatch', [$event], $event);

        $return = $led->dispatch('foo.bar', ['blubber']);
        $this->assertEquals([$event], $return);
    }

    public function testDispatchMixedEventData(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->expectException(ErrorException::class);
        $led->dispatch(new NullEvent(), [42]);
    }

    public function testPush(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->expectException(ErrorException::class);
        $led->push('foo.bar', [1337]);
    }

    public function testFlush(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->expectException(ErrorException::class);
        $led->flush('foo.bar');
    }

    public function testForget(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->setExpects($this->dispatcher, 'forget', ['event.to.forget']);

        $led->forget('event.to.forget');
    }

    public function testForgetPushed(): void
    {
        $led = new LaravelEventDispatcher($this->dispatcher);

        $this->expectException(ErrorException::class);
        $led->forgetPushed();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->createMock(EventDispatcher::class);
    }
}
