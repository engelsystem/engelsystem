<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\LegacyMiddleware;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Server\RequestHandlerInterface;

class LegacyMiddlewareTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\LegacyMiddleware::__construct
     * @covers \Engelsystem\Middleware\LegacyMiddleware::process
     */
    public function testProcess404(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        $request = new Request(['p' => 'notAvailablePage']);
        $this->app->instance('request', $request);

        $this->mockTranslator();

        $response = new Response();
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$this->app, $auth])
            ->onlyMethods(['renderPage'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('renderPage')
            ->with(404, 'page.404.title', 'page.404.text')
            ->willReturn($response);

        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\LegacyMiddleware::process
     */
    public function testProcess(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->exactly(2))
            ->method('can')
            ->withConsecutive(['users.arrive.list'], ['admin_arrive'])
            ->willReturnOnConsecutiveCalls(true, false);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => 'admin-arrive']);
        $this->app->instance('request', $request);

        $response = new Response();
        /** @var LegacyMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$this->app, $auth])
            ->onlyMethods(['loadPage', 'renderPage'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('loadPage')
            ->with('admin_arrive')
            ->willReturn(['title', 'content']);
        $middleware->expects($this->once())
            ->method('renderPage')
            ->with('admin_arrive', 'title', 'content')
            ->willReturn($response);

        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\LegacyMiddleware::process
     */
    public function testProcessResponseInterface(): void
    {
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->exactly(2))
            ->method('can')
            ->withConsecutive(['users.arrive.list'], ['admin_arrive'])
            ->willReturnOnConsecutiveCalls(true, false);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => 'admin-arrive']);
        $this->app->instance('request', $request);

        $responseInstance = new Response();
        /** @var LegacyMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$this->app, $auth])
            ->onlyMethods(['loadPage', 'renderPage'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('loadPage')
            ->with('admin_arrive')
            ->willReturn(['', $responseInstance]);

        $response = $middleware->process($request, $handler);
        $this->assertEquals($responseInstance, $response);
    }
}
