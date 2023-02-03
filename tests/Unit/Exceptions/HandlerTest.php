<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Environment;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Http\Request;
use ErrorException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handler::__construct()
     */
    public function testCreate(): void
    {
        /** @var Handler|MockObject $handler */
        $handler = new Handler();
        $this->assertInstanceOf(Handler::class, $handler);
        $this->assertEquals(Environment::PRODUCTION, $handler->getEnvironment());

        $anotherHandler = new Handler(Environment::DEVELOPMENT);
        $this->assertEquals(Environment::DEVELOPMENT, $anotherHandler->getEnvironment());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::errorHandler()
     */
    public function testErrorHandler(): void
    {
        /** @var Handler|MockObject $handler */
        $handler = $this->getMockBuilder(Handler::class)
            ->onlyMethods(['exceptionHandler'])
            ->getMock();

        $handler->expects($this->once())
            ->method('exceptionHandler')
            ->with($this->isInstanceOf(ErrorException::class));

        $handler->errorHandler(1, 'Foo and bar!', '/lo/rem.php', 123);
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::exceptionHandler()
     */
    public function testExceptionHandler(): void
    {
        $exception = new Exception();
        $errorMessage = 'Oh noes, an error!';

        /** @var HandlerInterface|MockObject $handlerMock */
        $handlerMock = $this->getMockForAbstractClass(HandlerInterface::class);
        $handlerMock->expects($this->atLeastOnce())
            ->method('report')
            ->with($exception);
        $handlerMock->expects($this->atLeastOnce())
            ->method('render')
            ->with($this->isInstanceOf(Request::class), $exception)
            ->willReturnCallback(function () use ($errorMessage): void {
                echo $errorMessage;
            });

        /** @var Handler|MockObject $handler */
        $handler = $this->getMockBuilder(Handler::class)
            ->onlyMethods(['terminateApplicationImmediately'])
            ->getMock();
        $handler->expects($this->once())
            ->method('terminateApplicationImmediately');

        $handler->setHandler(Environment::PRODUCTION, $handlerMock);

        $this->expectOutputString($errorMessage);
        $handler->exceptionHandler($exception);

        $return = $handler->exceptionHandler($exception, true);
        $this->assertEquals($errorMessage, $return);
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getEnvironment()
     * @covers \Engelsystem\Exceptions\Handler::setEnvironment()
     */
    public function testEnvironment(): void
    {
        $handler = new Handler();

        $handler->setEnvironment(Environment::DEVELOPMENT);
        $this->assertEquals(Environment::DEVELOPMENT, $handler->getEnvironment());

        $handler->setEnvironment(Environment::PRODUCTION);
        $this->assertEquals(Environment::PRODUCTION, $handler->getEnvironment());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getHandler()
     * @covers \Engelsystem\Exceptions\Handler::setHandler()
     */
    public function testHandler(): void
    {
        $handler = new Handler();
        /** @var HandlerInterface|MockObject $devHandler */
        $devHandler = $this->getMockForAbstractClass(HandlerInterface::class);
        /** @var HandlerInterface|MockObject $prodHandler */
        $prodHandler = $this->getMockForAbstractClass(HandlerInterface::class);

        $handler->setHandler(Environment::DEVELOPMENT, $devHandler);
        $handler->setHandler(Environment::PRODUCTION, $prodHandler);
        $this->assertEquals($devHandler, $handler->getHandler(Environment::DEVELOPMENT));
        $this->assertEquals($prodHandler, $handler->getHandler(Environment::PRODUCTION));
        $this->assertCount(2, $handler->getHandler());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getRequest()
     * @covers \Engelsystem\Exceptions\Handler::setRequest()
     */
    public function testRequest(): void
    {
        $handler = new Handler();
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);

        $handler->setRequest($request);
        $this->assertEquals($request, $handler->getRequest());
    }
}
