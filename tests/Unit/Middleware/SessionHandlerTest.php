<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\SessionHandler;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

#[CoversMethod(SessionHandler::class, '__construct')]
#[CoversMethod(SessionHandler::class, 'process')]
class SessionHandlerTest extends TestCase
{
    public function testProcess(): void
    {
        $sessionStorage = $this->createMock(NativeSessionStorage::class);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getStubBuilder(ResponseInterface::class)->getStub();

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $request->expects($this->exactly(2))
            ->method('getCookieParams')
            ->willReturnOnConsecutiveCalls([], ['SESSION' => 'BlaFoo']);

        $request->expects($this->exactly(2))
            ->method('getAttribute')
            ->with('route-api-accessible')
            ->willReturnOnConsecutiveCalls(true, false);

        $sessionStorage->expects($this->once())
            ->method('getName')
            ->willReturn('SESSION');

        $middleware = $this->getMockBuilder(SessionHandler::class)
            ->setConstructorArgs([$sessionStorage, ['/foo']])
            ->onlyMethods(['destroyNative'])
            ->getMock();

        $middleware->expects($this->once())
            ->method('destroyNative')
            ->willReturn(true);

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
