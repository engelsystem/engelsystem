<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Environment;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Http\Request;
use ErrorException;
use Exception;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Handler::class, '__construct')]
#[CoversMethod(Handler::class, 'errorHandler')]
#[CoversMethod(Handler::class, 'exceptionHandler')]
#[CoversMethod(Handler::class, 'getEnvironment')]
#[CoversMethod(Handler::class, 'setEnvironment')]
#[CoversMethod(Handler::class, 'getHandler')]
#[CoversMethod(Handler::class, 'setHandler')]
#[CoversMethod(Handler::class, 'getRequest')]
#[CoversMethod(Handler::class, 'setRequest')]
class HandlerTest extends TestCase
{
    public function testCreate(): void
    {
        $handler = new Handler();
        $this->assertInstanceOf(Handler::class, $handler);
        $this->assertEquals(Environment::PRODUCTION, $handler->getEnvironment());

        $anotherHandler = new Handler(Environment::DEVELOPMENT);
        $this->assertEquals(Environment::DEVELOPMENT, $anotherHandler->getEnvironment());
    }

    public static function errorHandlerProvider(): array
    {
        return [
            // Environment, Error level, should display message or (if false) ignore the output by returning

            [Environment::PRODUCTION, E_RECOVERABLE_ERROR, true],
            [Environment::PRODUCTION, E_WARNING, false],
            [Environment::PRODUCTION, E_NOTICE, false],
            [Environment::PRODUCTION, E_DEPRECATED, false],
            [Environment::PRODUCTION, E_COMPILE_ERROR, true],
            [Environment::PRODUCTION, E_USER_WARNING, false],
            [Environment::PRODUCTION, E_USER_NOTICE, false],
            [Environment::PRODUCTION, E_USER_DEPRECATED, false],

            [Environment::DEVELOPMENT, E_RECOVERABLE_ERROR, true],
            [Environment::DEVELOPMENT, E_WARNING, true],
            [Environment::DEVELOPMENT, E_NOTICE, true],
            [Environment::DEVELOPMENT, E_DEPRECATED, true],
            [Environment::DEVELOPMENT, E_COMPILE_ERROR, true],
            [Environment::DEVELOPMENT, E_USER_WARNING, true],
            [Environment::DEVELOPMENT, E_USER_NOTICE, true],
            [Environment::DEVELOPMENT, E_USER_DEPRECATED, true],
        ];
    }

    #[DataProvider('errorHandlerProvider')]
    public function testErrorHandler(Environment $env, int $level, bool $showError): void
    {
        $handler = $this->getMockBuilder(Handler::class)
            ->setConstructorArgs([$env])
            ->onlyMethods(['exceptionHandler'])
            ->getMock();

        $handler->expects($this->once())
            ->method('exceptionHandler')
            ->with($this->isInstanceOf(ErrorException::class), $showError);

        $return = $handler->errorHandler($level, 'Foo and bar!', '/lo/rem.php', 123);
        $this->assertEquals($showError, $return);
    }

    public function testExceptionHandler(): void
    {
        $exception = new Exception();
        $errorMessage = 'Oh noes, an error!';

        $handlerMock = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $handlerMock->expects($this->atLeastOnce())
            ->method('report')
            ->with($exception);
        $handlerMock->expects($this->atLeastOnce())
            ->method('render')
            ->with($this->isInstanceOf(Request::class), $exception)
            ->willReturnCallback(function () use ($errorMessage): void {
                echo $errorMessage;
            });

        $handler = $this->getMockBuilder(Handler::class)
            ->onlyMethods(['terminateApplicationImmediately'])
            ->getMock();
        $handler->expects($this->once())
            ->method('terminateApplicationImmediately');

        $handler->setHandler(Environment::PRODUCTION, $handlerMock);

        $this->expectOutputString($errorMessage);
        $handler->exceptionHandler($exception);

        $return = $handler->exceptionHandler($exception, false);
        $this->assertEquals($errorMessage, $return);
    }

    public function testEnvironment(): void
    {
        $handler = new Handler();

        $handler->setEnvironment(Environment::DEVELOPMENT);
        $this->assertEquals(Environment::DEVELOPMENT, $handler->getEnvironment());

        $handler->setEnvironment(Environment::PRODUCTION);
        $this->assertEquals(Environment::PRODUCTION, $handler->getEnvironment());
    }

    public function testHandler(): void
    {
        $handler = new Handler();
        $devHandler = $this->getStubBuilder(HandlerInterface::class)->getStub();
        $prodHandler = $this->getStubBuilder(HandlerInterface::class)->getStub();

        $handler->setHandler(Environment::DEVELOPMENT, $devHandler);
        $handler->setHandler(Environment::PRODUCTION, $prodHandler);
        $this->assertEquals($devHandler, $handler->getHandler(Environment::DEVELOPMENT));
        $this->assertEquals($prodHandler, $handler->getHandler(Environment::PRODUCTION));
        $this->assertCount(2, $handler->getHandler());
    }

    public function testRequest(): void
    {
        $handler = new Handler();
        $request = $this->createStub(Request::class);

        $handler->setRequest($request);
        $this->assertEquals($request, $handler->getRequest());
    }
}
