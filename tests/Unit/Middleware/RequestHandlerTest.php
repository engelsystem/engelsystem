<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\CallableHandler;
use Engelsystem\Middleware\RequestHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ControllerImplementation;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass as Reflection;
use TypeError;

#[CoversMethod(RequestHandler::class, '__construct')]
#[CoversMethod(RequestHandler::class, 'process')]
#[CoversMethod(RequestHandler::class, 'resolveRequestHandler')]
#[CoversMethod(RequestHandler::class, 'checkPermissions')]
#[AllowMockObjectsWithoutExpectations]
class RequestHandlerTest extends TestCase
{
    public function testInit(): void
    {
        $container = $this->createStub(Application::class);

        $handler = new RequestHandler($container);

        $reflection = new Reflection(get_class($handler));
        $property = $reflection->getProperty('container');

        $this->assertEquals($container, $property->getValue($handler));
    }

    public function testProcess(): void
    {
        list($container, $request, $handler, $response, $middlewareInterface) = $this->getMocks();

        $requestHandlerInterface = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $request->expects($this->exactly(3))
            ->method('getAttribute')
            ->with('route-request-handler')
            ->willReturn('FooBarClass');

        $middleware = $this->getMockBuilder(RequestHandler::class)
            ->setConstructorArgs([$container])
            ->onlyMethods(['resolveRequestHandler'])
            ->getMock();
        $middleware->expects($this->exactly(3))
            ->method('resolveRequestHandler')
            ->with('FooBarClass')
            ->willReturnOnConsecutiveCalls(
                $middlewareInterface,
                $requestHandlerInterface,
                null
            );

        $middlewareInterface->expects($this->once())
            ->method('process')
            ->with($request, $handler)
            ->willReturn($response);
        $requestHandlerInterface->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $return = $middleware->process($request, $handler);
        $this->assertEquals($return, $response);

        $return = $middleware->process($request, $handler);
        $this->assertEquals($return, $response);

        $this->expectException(TypeError::class);
        $middleware->process($request, $handler);
    }

    public function testResolveRequestHandler(): void
    {
        list($container, $request, $handler, $response, $middlewareInterface) = $this->getMocks();

        $className = 'Engelsystem\\Controllers\\FooBarTestController';

        $request->expects($this->exactly(1))
            ->method('getAttribute')
            ->with('route-request-handler')
            ->willReturn('FooBarTestController@process');

        $middleware = $this->getMockBuilder(RequestHandler::class)
            ->setConstructorArgs([$container])
            ->onlyMethods(['resolveMiddleware'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('resolveMiddleware')
            ->with([$middlewareInterface, 'process'])
            ->willReturn($middlewareInterface);

        $middlewareInterface->expects($this->once())
            ->method('process')
            ->with($request, $handler)
            ->willReturn($response);

        $matcher = $this->exactly(2);
        $container->expects($matcher)
            ->method('has')->willReturnCallback(function (...$parameters) use ($matcher, $className) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('FooBarTestController', $parameters[0]);
                    return false;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($className, $parameters[0]);
                    return true;
                }
            });
        $container->expects($this->once())
            ->method('make')
            ->with($className)
            ->willReturn($middlewareInterface);
        $matcher = $this->exactly(2);
        $container->expects($matcher)
            ->method('instance')->willReturnCallback(function (...$parameters) use ($matcher, $request): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(ServerRequestInterface::class, $parameters[0]);
                    $this->assertSame($request, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('request', $parameters[0]);
                    $this->assertSame($request, $parameters[1]);
                }
            });

        $return = $middleware->process($request, $handler);
        $this->assertEquals($return, $response);
    }

    public function testCheckPermissions(): void
    {
        list($container, $request, $handler, $response) = $this->getMocks();

        $auth = $this->createMock(Authenticator::class);

        $class = new ControllerImplementation();
        $callable = $this->getMockBuilder(CallableHandler::class)
            ->setConstructorArgs([[$class, 'actionStub']])
            ->getMock();

        $callable->expects($this->exactly(2))
            ->method('getCallable')
            ->willReturn([$class, 'actionStub']);

        $callable->expects($this->exactly(1))
            ->method('process')
            ->with($request, $handler)
            ->willReturn($response);

        $request->expects($this->exactly(2))
            ->method('getAttribute')
            ->with('route-request-handler')
            ->willReturn($callable);

        $middleware = $this->getMockBuilder(RequestHandler::class)
            ->setConstructorArgs([$container])
            ->onlyMethods(['resolveRequestHandler'])
            ->getMock();

        $middleware->expects($this->exactly(2))
            ->method('resolveRequestHandler')
            ->with($callable)
            ->willReturn($callable);

        $container->expects($this->exactly(2))
            ->method('get')
            ->with('auth')
            ->willReturn($auth);

        $hasPermissions = [];
        $auth->expects($this->atLeastOnce())
            ->method('can')
            ->willReturnCallback(function ($permission) use (&$hasPermissions) {
                return in_array($permission, $hasPermissions);
            });

        $hasPermissions = ['foo', 'test', 'user'];
        $class->setPermissions([
            'foo',
            'loremIpsumAction' => ['dolor', 'sit'],
            'actionStub'       => ['test'],
            'user',
        ]);
        $middleware->process($request, $handler);

        $class->setPermissions(array_merge(['not.existing.permission'], $hasPermissions));
        $this->expectException(HttpForbidden::class);
        $middleware->process($request, $handler);
    }

    public function testCheckPermissionsAnyForbidden(): void
    {
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'canAny', [['foo', 'bar', 'baz']], false);
        $this->app->instance('auth', $auth);

        $controller = new ControllerImplementation();
        $request = (new Request())
            ->withAttribute('route-request-handler', [$controller, 'actionStub']);

        $middleware = new RequestHandler($this->app);

        $controller->setPermissions(['foo||bar||baz']);
        $this->expectException(HttpForbidden::class);
        $middleware->process($request, $handler);
    }

    public function testCheckPermissionsAny(): void
    {
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'canAny', [['foo', 'bar', 'baz']], true);
        $this->app->instance('auth', $auth);

        $controller = new ControllerImplementation();
        $request = (new Request())
            ->withAttribute('route-request-handler', [$controller, 'actionStub']);

        $middleware = new RequestHandler($this->app);

        $controller->setPermissions(['actionStub' => 'foo||bar||baz']);
        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckPermissionsHasPermission(): void
    {
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        $auth = $this->createMock(Authenticator::class);
        $this->app->instance('auth', $auth);

        $controller = new ControllerImplementation();
        $request = (new Request())
            ->withAttribute('route-request-handler', [$controller, 'allow']);

        $middleware = new RequestHandler($this->app);

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('yay', $response->getBody()->getContents());

        $request = (new Request())
            ->withAttribute('route-request-handler', [$controller, 'deny']);
        $this->expectException(HttpForbidden::class);
        $middleware->process($request, $handler);
    }

    /**
     * @return array{
     *     Application&MockObject,
     *     ServerRequestInterface&MockObject,
     *     RequestHandlerInterface&MockObject,
     *     ResponseInterface&MockObject,
     *     MiddlewareInterface&MockObject,
     * }
     */
    protected function getMocks(): array
    {
        $container = $this->createMock(Application::class);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middlewareInterface = $this->getMockBuilder(MiddlewareInterface::class)->getMock();

        return [$container, $request, $handler, $response, $middlewareInterface];
    }
}
