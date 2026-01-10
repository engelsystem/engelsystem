<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Config\Config;
use Engelsystem\Middleware\LegacyMiddleware;
use Engelsystem\Middleware\RouteDispatcher;
use Engelsystem\Middleware\RouteDispatcherServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use FastRoute\Dispatcher as FastRouteDispatcher;
use Illuminate\Contracts\Container\ContextualBindingBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Server\MiddlewareInterface;

#[CoversMethod(RouteDispatcherServiceProvider::class, 'register')]
class RouteDispatcherServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $bindingBuilder = $this->createMock(ContextualBindingBuilder::class);
        $routeDispatcher = $this->createStub(FastRouteDispatcher::class);
        $config = new Config(['environment' => 'development']);

        $app = $this->getAppMock(['alias', 'when', 'get']);

        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($matcher, $config) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('config', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('path.cache.routes', $parameters[0]);
                }
                return $config;
            });

        $app->expects($this->once())
            ->method('alias')
            ->with(RouteDispatcher::class, 'route.dispatcher');

        $app->expects($this->exactly(2))
            ->method('when')
            ->with(RouteDispatcher::class)
            ->willReturn($bindingBuilder);

        $matcher = $this->exactly(2);
        $bindingBuilder->expects($matcher)
            ->method('needs')->willReturnCallback(function (...$parameters) use ($matcher, $bindingBuilder) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(FastRouteDispatcher::class, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(MiddlewareInterface::class, $parameters[0]);
                }
                return $bindingBuilder;
            });

        $bindingBuilder->expects($this->exactly(2))
            ->method('give')
            ->with($this->callback(function ($subject) {
                if (is_callable($subject)) {
                    $subject();
                }

                return is_callable($subject) || $subject == LegacyMiddleware::class;
            }));

        $serviceProvider = $this->getMockBuilder(RouteDispatcherServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['generateRouting'])
            ->getMock();

        $serviceProvider->expects($this->once())
            ->method('generateRouting')
            ->willReturn($routeDispatcher);

        $serviceProvider->register();
    }
}
