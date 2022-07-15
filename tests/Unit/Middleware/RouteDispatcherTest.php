<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Middleware\RouteDispatcher;
use FastRoute\Dispatcher as FastRouteDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatcherTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\RouteDispatcher::__construct
     * @covers \Engelsystem\Middleware\RouteDispatcher::process
     */
    public function testProcess()
    {
        /** @var FastRouteDispatcher|MockObject $dispatcher */
        /** @var ResponseInterface|MockObject $response */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        list($dispatcher, $response, $request, $handler) = $this->getMocks();

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('HEAD', '/foo!bar')
            ->willReturn([FastRouteDispatcher::FOUND, $handler, ['foo' => 'bar', 'lorem' => 'ipsum']]);

        $request->expects($this->exactly(4))
            ->method('withAttribute')
            ->withConsecutive(
                ['route-request-handler', $handler],
                ['route-request-path', '/foo!bar'],
                ['foo', 'bar'],
                ['lorem', 'ipsum']
            )
            ->willReturn($request);

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new RouteDispatcher($dispatcher, $response);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\RouteDispatcher::process
     */
    public function testProcessNotFound()
    {
        /** @var FastRouteDispatcher|MockObject $dispatcher */
        /** @var ResponseInterface|MockObject $response */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        list($dispatcher, $response, $request, $handler) = $this->getMocks();
        /** @var MiddlewareInterface|MockObject $notFound */
        $notFound = $this->createMock(MiddlewareInterface::class);

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->with('HEAD', '/foo!bar')
            ->willReturn([FastRouteDispatcher::NOT_FOUND]);

        $response->expects($this->once())
            ->method('withStatus')
            ->with(404)
            ->willReturn($response);

        $notFound->expects($this->once())
            ->method('process')
            ->with($request, $handler)
            ->willReturn($response);

        $middleware = new RouteDispatcher($dispatcher, $response, $notFound);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);

        $middleware = new RouteDispatcher($dispatcher, $response);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\RouteDispatcher::process
     */
    public function testProcessNotAllowed()
    {
        /** @var FastRouteDispatcher|MockObject $dispatcher */
        /** @var ResponseInterface|MockObject $response */
        /** @var ServerRequestInterface|MockObject $request */
        /** @var RequestHandlerInterface|MockObject $handler */
        list($dispatcher, $response, $request, $handler) = $this->getMocks();

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('HEAD', '/foo!bar')
            ->willReturn([FastRouteDispatcher::METHOD_NOT_ALLOWED, ['POST', 'TEST']]);

        $response->expects($this->once())
            ->method('withStatus')
            ->with(405)
            ->willReturn($response);
        $response->expects($this->once())
            ->method('withHeader')
            ->with('Allow', 'POST, TEST')
            ->willReturn($response);

        $middleware = new RouteDispatcher($dispatcher, $response);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
    }

    /**
     * @return array
     */
    protected function getMocks(): array
    {
        /** @var FastRouteDispatcher|MockObject $dispatcher */
        $dispatcher = $this->getMockForAbstractClass(FastRouteDispatcher::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $request->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('HEAD');
        $request->expects($this->atLeastOnce())
            ->method('getUri')
            ->willReturn('http://foo.bar/lorem/foo%21bar');
        $request->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn('/foo%21bar');

        return [$dispatcher, $response, $request, $handler];
    }
}
