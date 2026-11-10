<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\LaravelEvent;
use Engelsystem\Events\Listener\Eloquent;
use Engelsystem\Events\ModelEvent;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Eloquent::class, '__construct')]
#[CoversMethod(Eloquent::class, 'handle')]
class EloquentTest extends TestCase
{
    public function testHandle(): void
    {
        $model = new Shift();
        $event = new LaravelEvent('eloquent.saving: Engelsystem\Models\Shifts\Shift', $model);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($model) {
                $this->assertInstanceOf(ModelEvent::class, $event);
                $this->assertEquals('model.shifts.shift.saving', $event->getName());
                $this->assertEquals($model, $event->model);
                return $event;
            });

        $listener = new Eloquent($this->app, $dispatcher);
        $listener->handle($event);
    }


    public function testHandleIgnoreOthers(): void
    {
        $model = new Shift();
        $event = new LaravelEvent('eloquent.retrieved: Engelsystem\Models\Shifts\Shift', $model);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->setExpects($dispatcher, 'dispatch', null, null, $this->never());

        $listener = new Eloquent($this->app, $dispatcher);
        $listener->handle($event);
    }
}
