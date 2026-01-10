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
        $container = $this->getStubBuilder(ContainerInterface::class)->getStub();
        $request = $this->getStubBuilder(ServerRequestInterface::class)->getStub();
        $response = $this->getMockBuilder(Response::class)->getMock();
        $errorHandler = $this->getStubBuilder(Handler::class)->getStub();
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);
        $throwExceptionHandler = new ExceptionMiddlewareHandler();

        Application::setInstance($container);

        $container->method('get')
            ->willReturnMap([
                ['error.handler', $errorHandler],
                ['psr7.response', $response],
            ]);

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
