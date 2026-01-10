<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\ExceptionHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ExceptionMiddlewareHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddlewareHandler;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversMethod(ExceptionHandler::class, '__construct')]
#[CoversMethod(ExceptionHandler::class, 'process')]
class ExceptionHandlerTest extends TestCase
{
    public function testRegister(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $request = $this->getStubBuilder(ServerRequestInterface::class)->getStub();
        $response = $this->getMockBuilder(Response::class)->getMock();
        $errorHandler = $this->getStubBuilder(Handler::class)->getStub();
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);
        $throwExceptionHandler = new ExceptionMiddlewareHandler();

        Application::setInstance($container);

        $matcher = $this->exactly(2);
        $container->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($response, $errorHandler, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('error.handler', $parameters[0]);
                    return $errorHandler;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('psr7.response', $parameters[0]);
                    return $response;
                }
            });

        $response->expects($this->once())
            ->method('withContent')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('withStatus')
            ->with(500)
            ->willReturn($response);

        $handler = new ExceptionHandler($container);
        $return = $handler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return);

        $return = $handler->process($request, $throwExceptionHandler);
        $this->assertEquals($response, $return);
    }
}
