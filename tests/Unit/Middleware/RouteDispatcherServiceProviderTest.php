<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\LegacyMiddleware;
use Engelsystem\Middleware\RouteDispatcher;
use Engelsystem\Middleware\RouteDispatcherServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use FastRoute\Dispatcher as FastRouteDispatcher;
use Illuminate\Contracts\Container\ContextualBindingBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Server\MiddlewareInterface;

class RouteDispatcherServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Middleware\RouteDispatcherServiceProvider::register()
     */
    public function testRegister()
    {
        $bindingBuilder = $this->createMock(ContextualBindingBuilder::class);
        $routeDispatcher = $this->getMockForAbstractClass(FastRouteDispatcher::class);

        $app = $this->getApp(['alias', 'when']);

        $app->expects($this->once())
            ->method('alias')
            ->with(RouteDispatcher::class, 'route.dispatcher');

        $app->expects($this->exactly(2))
            ->method('when')
            ->with(RouteDispatcher::class)
            ->willReturn($bindingBuilder);

        $bindingBuilder->expects($this->exactly(2))
            ->method('needs')
            ->withConsecutive(
                [FastRouteDispatcher::class],
                [MiddlewareInterface::class]
            )
            ->willReturn($bindingBuilder);

        $bindingBuilder->expects($this->exactly(2))
            ->method('give')
            ->with($this->callback(function ($subject) {
                if (is_callable($subject)) {
                    $subject();
                }

                return is_callable($subject) || $subject == LegacyMiddleware::class;
            }));

        /** @var RouteDispatcherServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(RouteDispatcherServiceProvider::class)
            ->setConstructorArgs([$app])
            ->setMethods(['generateRouting'])
            ->getMock();

        $serviceProvider->expects($this->once())
            ->method('generateRouting')
            ->willReturn($routeDispatcher);

        $serviceProvider->register();
    }
}
