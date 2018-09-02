<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Middleware\RequestHandler;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass as Reflection;

class RequestHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\RequestHandler::__construct
     */
    public function testInit()
    {
        /** @var Application|MockObject $container */
        $container = $this->createMock(Application::class);

        $handler = new RequestHandler($container);

        $reflection = new Reflection(get_class($handler));
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);

        $this->assertEquals($container, $property->getValue($handler));
    }

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::process
     */
    public function testProcess()
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
            ->setMethods(['resolveRequestHandler'])
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

        $middleware->process($request, $handler);
        $this->assertEquals($return, $response);

        $this->expectException(InvalidArgumentException::class);
        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\RequestHandler::resolveRequestHandler
     */
    public function testResolveRequestHandler()
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
            ->willReturn('FooBarTestController@showStuff');

        /** @var RequestHandler|MockObject $middleware */
        $middleware = $this->getMockBuilder(RequestHandler::class)
            ->setConstructorArgs([$container])
            ->setMethods(['resolveMiddleware'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('resolveMiddleware')
            ->with([$middlewareInterface, 'showStuff'])
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

        $return = $middleware->process($request, $handler);
        $this->assertEquals($return, $response);
    }

    /**
     * @return array
     */
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

        return array($container, $request, $handler, $response, $middlewareInterface);
    }
}
