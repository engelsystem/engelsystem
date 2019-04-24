<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\SessionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\SessionHandler::__construct
     * @covers \Engelsystem\Middleware\SessionHandler::process
     */
    public function testProcess()
    {
        /** @var NativeSessionStorage|MockObject $sessionStorage */
        $sessionStorage = $this->createMock(NativeSessionStorage::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $request->expects($this->exactly(2))
            ->method('getCookieParams')
            ->willReturnOnConsecutiveCalls([], ['SESSION' => 'BlaFoo']);

        $request->expects($this->exactly(2))
            ->method('getAttribute')
            ->with('route-request-path')
            ->willReturn('/foo');

        $sessionStorage->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('SESSION');

        /** @var SessionHandler|MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionHandler::class)
            ->setConstructorArgs([$sessionStorage, ['/foo']])
            ->setMethods(['destroyNative'])
            ->getMock();

        $middleware->expects($this->once())
            ->method('destroyNative')
            ->willReturn(true);

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
