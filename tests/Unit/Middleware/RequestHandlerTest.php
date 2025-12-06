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
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass as Reflection;
use TypeError;

class RequestHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\RequestHandler::__construct
     */
    public function testInit(): void
    {
        /** @var Application|MockObject $container */
        $container = $this->createMock(Application::class);

        $handler = new RequestHandler($container);

        $reflection = new Reflection(get_class($handler));
        $property = $reflection->getProperty('container');

        $this->assertEquals($container, $property->getValue($handler));
    }

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::process
     */
    public function testProcess(): void
    {
        /** @var Application|MockObject $container */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        /** @var ResponseInterface|MockObject $response */
        /** @var MiddlewareInterface|MockObject $middlewareInterface */
        list($container, $request, $handler, $response, $middlewareInterface) = $this->getMocks();

        $requestHandlerInterface = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $request->expects($this->exactly(3))
            ->method('getAttribute')
            ->with('route-request-handler')
            ->willReturn('FooBarClass');

        /** @var RequestHandler|MockObject $middleware */
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

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::resolveRequestHandler
     */
    public function testResolveRequestHandler(): void
    {
        /** @var Application|MockObject $container */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        /** @var ResponseInterface|MockObject $response */
        /** @var MiddlewareInterface|MockObject $middlewareInterface */
        list($container, $request, $handler, $response, $middlewareInterface) = $this->getMocks();

        $className = 'Engelsystem\\Controllers\\FooBarTestController';

        $request->expects($this->exactly(1))
            ->method('getAttribute')
            ->with('route-request-handler')
            ->willReturn('FooBarTestController@process');

        /** @var RequestHandler|MockObject $middleware */
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

        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['FooBarTestController'], [$className])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects($this->once())
            ->method('make')
            ->with($className)
            ->willReturn($middlewareInterface);
        $container->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive([ServerRequestInterface::class, $request], ['request', $request]);

        $return = $middleware->process($request, $handler);
        $this->assertEquals($return, $response);
    }

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::checkPermissions
     * @covers \Engelsystem\Middleware\RequestHandler::process
     */
    public function testCheckPermissions(): void
    {
        /** @var Application|MockObject $container */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        /** @var ResponseInterface|MockObject $response */
        list($container, $request, $handler, $response) = $this->getMocks();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        $class = new ControllerImplementation();
        /** @var CallableHandler|MockObject $callable */
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


        /** @var RequestHandler|MockObject $middleware */
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

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::checkPermissions
     */
    public function testCheckPermissionsAnyForbidden(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        /** @var Authenticator|MockObject $auth */
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

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::checkPermissions
     */
    public function testCheckPermissionsAny(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        /** @var Authenticator|MockObject $auth */
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

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::checkPermissions
     */
    public function testCheckPermissionsHasPermission(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        list(, , $handler) = $this->getMocks();
        $this->app->instance('response', new Response());
        /** @var Authenticator|MockObject $auth */
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

    protected function getMocks(): array
    {
        /** @var Application|MockObject $container */
        $container = $this->createMock(Application::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var MiddlewareInterface $middlewareInterface */
        $middlewareInterface = $this->getMockForAbstractClass(MiddlewareInterface::class);

        return [$container, $request, $handler, $response, $middlewareInterface];
    }
}
