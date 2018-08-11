<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Middleware\Dispatcher;
use Engelsystem\Test\Unit\Middleware\Stub\NotARealMiddleware;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddleware;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass as Reflection;

class DispatcherTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\Dispatcher::__construct
     */
    public function testInit()
    {
        /** @var Application|MockObject $container */
        $container = $this->createMock(Application::class);

        $dispatcher = new Dispatcher([], $container);
        $this->assertInstanceOf(MiddlewareInterface::class, $dispatcher);
        $this->assertInstanceOf(RequestHandlerInterface::class, $dispatcher);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $this->assertEquals($container, $property->getValue($dispatcher));
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::process
     */
    public function testProcess()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);

        /** @var Dispatcher|MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->setMethods(['handle'])
            ->getMock();

        $dispatcher->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $return = $dispatcher->process($request, $handler);
        $this->assertEquals($response, $return);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('next');
        $property->setAccessible(true);

        $this->assertEquals($handler, $property->getValue($dispatcher));
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::handle
     */
    public function testHandle()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var MiddlewareInterface|MockObject $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);

        $dispatcher = new Dispatcher([$middleware]);
        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $dispatcher)
            ->willReturn($response);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::handle
     */
    public function testHandleNext()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);

        $dispatcher = new Dispatcher();
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $reflection = new Reflection(get_class($dispatcher));
        $property = $reflection->getProperty('next');
        $property->setAccessible(true);
        $property->setValue($dispatcher, $handler);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::handle
     */
    public function testHandleNoMiddleware()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->expectException(LogicException::class);

        $dispatcher = new Dispatcher();
        $dispatcher->handle($request);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::handle
     */
    public function testHandleNoRealMiddleware()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $dispatcher = new Dispatcher([new NotARealMiddleware()]);
        $dispatcher->handle($request);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::handle
     */
    public function testHandleCallResolve()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var MiddlewareInterface|MockObject $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);

        /** @var Dispatcher|MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->setConstructorArgs([[MiddlewareInterface::class]])
            ->setMethods(['resolveMiddleware'])
            ->getMock();

        $dispatcher->expects($this->once())
            ->method('resolveMiddleware')
            ->with(MiddlewareInterface::class)
            ->willReturn($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $dispatcher)
            ->willReturn($response);

        $return = $dispatcher->handle($request);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::resolveMiddleware
     * @covers \Engelsystem\Middleware\Dispatcher::setContainer
     */
    public function testResolveMiddleware()
    {
        /** @var Application|MockObject $container */
        $container = $this->createMock(Application::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        $returnResponseMiddleware = new ReturnResponseMiddleware($response);

        $container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([ReturnResponseMiddleware::class], ['middleware'])
            ->willReturnOnConsecutiveCalls(false, true);

        $container->expects($this->once())
            ->method('make')
            ->with(ReturnResponseMiddleware::class)
            ->willReturn($returnResponseMiddleware);

        $container->expects($this->once())
            ->method('get')
            ->with('middleware')
            ->willReturn($returnResponseMiddleware);

        $dispatcher = new Dispatcher([ReturnResponseMiddleware::class]);
        $dispatcher->setContainer($container);
        $dispatcher->handle($request);

        $dispatcher = new Dispatcher(['middleware'], $container);
        $dispatcher->handle($request);
    }

    /**
     * @covers \Engelsystem\Middleware\Dispatcher::resolveMiddleware
     */
    public function testResolveMiddlewareNoContainer()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $dispatcher = new Dispatcher([ReturnResponseMiddleware::class]);
        $dispatcher->handle($request);
    }
}
