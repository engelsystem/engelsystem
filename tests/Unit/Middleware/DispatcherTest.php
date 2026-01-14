<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\Dispatcher;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddlewareHandler;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass as Reflection;
use TypeError;

#[CoversMethod(Dispatcher::class, '__construct')]
#[CoversMethod(Dispatcher::class, 'process')]
#[CoversMethod(Dispatcher::class, 'handle')]
#[CoversMethod(Dispatcher::class, 'setContainer')]
class DispatcherTest extends TestCase
{
    public function testInit(): void
    {
        $container = $this->createStub(Application::class);

        $dispatcher = new Dispatcher([], $container);
        $this->assertInstanceOf(MiddlewareInterface::class, $dispatcher);
        $this->assertInstanceOf(RequestHandlerInterface::class, $dispatcher);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('container');
        $this->assertEquals($container, $property->getValue($dispatcher));
    }

    public function testProcess(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $handler = $this->createStub(RequestHandlerInterface::class);

        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->onlyMethods(['handle'])
            ->getMock();

        $dispatcher->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $return = $dispatcher->process($request, $handler);
        $this->assertEquals($response, $return);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('next');

        $this->assertEquals($handler, $property->getValue($dispatcher));
    }

    public function testHandle(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);

        $dispatcher = new Dispatcher([$middleware]);
        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $dispatcher)
            ->willReturn($response);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);
    }

    public function testHandleNext(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $dispatcher = new Dispatcher();
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('next');
        $property->setValue($dispatcher, $handler);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);
    }

    public function testHandleNoMiddleware(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);

        $this->expectException(LogicException::class);

        $dispatcher = new Dispatcher();
        $dispatcher->handle($request);
    }

    public function testHandleCallResolve(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);

        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->setConstructorArgs([[MiddlewareInterface::class, MiddlewareInterface::class]])
            ->onlyMethods(['resolveMiddleware'])
            ->getMock();

        $dispatcher->expects($this->exactly(2))
            ->method('resolveMiddleware')
            ->with(MiddlewareInterface::class)
            ->willReturnOnConsecutiveCalls($middleware, null);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $dispatcher)
            ->willReturn($response);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);

        $this->expectException(TypeError::class);
        $dispatcher->handle($request);
    }

    public function testHandleCallResolveInvalidTypeResolved(): void
    {
        $instance = new Dispatcher([new ReturnResponseMiddlewareHandler(new Response())]);

        $this->expectException(InvalidArgumentException::class);
        $instance->handle(new Request());
    }

    public function testSetContainer(): void
    {
        $container = $this->createStub(Application::class);

        $middleware = new Dispatcher();
        $middleware->setContainer($container);

        $reflection = new Reflection(get_class($middleware));
        $property = $reflection->getProperty('container');

        $this->assertEquals($container, $property->getValue($middleware));
    }
}
