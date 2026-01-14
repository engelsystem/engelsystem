<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Middleware\RouteDispatcher;
use FastRoute\Dispatcher as FastRouteDispatcher;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(RouteDispatcher::class, '__construct')]
#[CoversMethod(RouteDispatcher::class, 'process')]
class RouteDispatcherTest extends TestCase
{
    public function testProcess(): void
    {
        list($dispatcher, $response, $request, $handler) = $this->getMocks();

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('HEAD', '/foo!bar')
            ->willReturn([FastRouteDispatcher::FOUND, $handler, ['foo' => 'bar', 'lorem' => 'ipsum']]);

        $response->expects($this->never())
            ->method('withStatus');

        $matcher = $this->exactly(4);
        $request->expects($matcher)
            ->method('withAttribute')->willReturnCallback(function (...$parameters) use ($matcher, $handler, $request) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('route-request-handler', $parameters[0]);
                    $this->assertSame($handler, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('route-request-path', $parameters[0]);
                    $this->assertSame('/foo!bar', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('foo', $parameters[0]);
                    $this->assertSame('bar', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('lorem', $parameters[0]);
                    $this->assertSame('ipsum', $parameters[1]);
                }
                return $request;
            });

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new RouteDispatcher($dispatcher, $response);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
    }

    public function testProcessNotFound(): void
    {
        list($dispatcher, $response, $request, $handler) = $this->getMocks();
        $notFound = $this->createMock(MiddlewareInterface::class);

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->with('HEAD', '/foo!bar')
            ->willReturn([FastRouteDispatcher::NOT_FOUND]);

        $response->expects($this->once())
            ->method('withStatus')
            ->with(404)
            ->willReturn($response);

        $handler->expects($this->never())
            ->method('handle');

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

    public function testProcessNotAllowed(): void
    {
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

        $handler->expects($this->never())
            ->method('handle');

        $middleware = new RouteDispatcher($dispatcher, $response);
        $return = $middleware->process($request, $handler);
        $this->assertEquals($response, $return);
    }

    /**
     * @return array{
     *     FastRouteDispatcher&MockObject,
     *     ResponseInterface&MockObject,
     *     ServerRequestInterface&MockObject,
     *     RequestHandlerInterface&MockObject,
     * }
     */
    protected function getMocks(): array
    {
        $dispatcher = $this->getMockBuilder(FastRouteDispatcher::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $request = $this->createMock(Request::class);
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $request->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('HEAD');
        $request->expects($this->atLeastOnce())
            ->method('getUri')
            ->willReturn('https://foo.bar/lorem/foo%21bar');
        $request->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturn('/foo%21bar');

        return [$dispatcher, $response, $request, $handler];
    }
}
