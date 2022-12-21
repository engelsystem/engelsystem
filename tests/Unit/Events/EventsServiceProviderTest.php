<?php

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Config\Config;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\EventsServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;

class EventsServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Events\EventsServiceProvider::register
     * @covers \Engelsystem\Events\EventsServiceProvider::registerEvents
     */
    public function testRegister(): void
    {
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance(EventDispatcher::class, $dispatcher);
        $dispatcher->expects($this->exactly(3))
            ->method('listen')
            ->withConsecutive(
                ['test.event', 'someFunction'],
                ['another.event', 'Foo\Bar@baz'],
                ['another.event', [$this, 'testRegister']]
            );

        $config = new Config([
            'event-handlers' => [
                'test.event' => 'someFunction',
                'another.event' => ['Foo\Bar@baz', [$this, 'testRegister']]
            ]
        ]);
        $this->app->instance('config', $config);

        /** @var EventsServiceProvider $provider */
        $provider = $this->app->make(EventsServiceProvider::class);

        $provider->register();
    }
}
