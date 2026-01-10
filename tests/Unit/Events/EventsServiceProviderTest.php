<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Config\Config;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\EventsServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(EventsServiceProvider::class, 'register')]
#[CoversMethod(EventsServiceProvider::class, 'registerEvents')]
class EventsServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance(EventDispatcher::class, $dispatcher);
        $matcher = $this->exactly(3);
        $dispatcher->expects($matcher)
            ->method('listen')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('test.event', $parameters[0]);
                    $this->assertSame('someFunction', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('another.event', $parameters[0]);
                    $this->assertSame('Foo\Bar@baz', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('another.event', $parameters[0]);
                    $this->assertSame([$this, 'testRegister'], $parameters[1]);
                }
            });

        $config = new Config([
            'event-handlers' => [
                'test.event' => 'someFunction',
                'another.event' => ['Foo\Bar@baz', [$this, 'testRegister']],
            ],
        ]);
        $this->app->instance('config', $config);

        /** @var EventsServiceProvider $provider */
        $provider = $this->app->make(EventsServiceProvider::class);

        $provider->register();
    }
}
