<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
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
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $request = new Request(['p' => 'notAvailablePage']);
        $this->app->instance('request', $request);

        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects($this->exactly(2))
            ->method('translate')
            ->withConsecutive(['page.404.title'], ['page.404.text'])
            ->willReturnOnConsecutiveCalls('page.404.title', 'page.404.text');
        $this->app->instance('translator', $translator);

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
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => 'public-dashboard']);
        $this->app->instance('request', $request);

        $response = new Response();
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$this->app, $auth])
            ->onlyMethods(['loadPage', 'renderPage'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('loadPage')
            ->with('public_dashboard')
            ->willReturn(['title', 'content']);
        $middleware->expects($this->once())
            ->method('renderPage')
            ->with('public_dashboard', 'title', 'content')
            ->willReturn($response);

        $middleware->process($request, $handler);
    }
}
