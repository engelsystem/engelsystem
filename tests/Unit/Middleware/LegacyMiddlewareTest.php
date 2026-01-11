<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\LegacyMiddleware;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(LegacyMiddleware::class, '__construct')]
#[CoversMethod(LegacyMiddleware::class, 'process')]
class LegacyMiddlewareTest extends TestCase
{
    public function testProcess404(): void
    {
        $handler = $this->getStubBuilder(RequestHandlerInterface::class)->getStub();
        $auth = $this->createStub(Authenticator::class);

        $request = new Request(['p' => 'notAvailablePage']);
        $this->app->instance('request', $request);

        $this->stubTranslator();

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

    public function testProcess(): void
    {
        $handler = $this->getStubBuilder(RequestHandlerInterface::class)->getStub();

        $auth = $this->createMock(Authenticator::class);
        $matcher = $this->exactly(2);
        $auth->expects($matcher)
            ->method('can')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('users.arrive.list', $parameters[0]);
                    return true;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('admin_arrive', $parameters[0]);
                    return false;
                }
            });

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => 'admin-arrive']);
        $this->app->instance('request', $request);

        $response = new Response();
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

    public function testProcessResponseInterface(): void
    {
        $handler = $this->getStubBuilder(RequestHandlerInterface::class)->getStub();

        $auth = $this->createMock(Authenticator::class);
        $matcher = $this->exactly(2);
        $auth->expects($matcher)
            ->method('can')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('users.arrive.list', $parameters[0]);
                    return true;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('admin_arrive', $parameters[0]);
                    return false;
                }
            });

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => 'admin-arrive']);
        $this->app->instance('request', $request);

        $responseInstance = new Response();
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
