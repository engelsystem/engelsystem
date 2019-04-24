<?php

namespace Engelsystem\Test\Unit\Exceptions;

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
    public function testCreate()
    {
        /** @var Handler|MockObject $handler */
        $handler = new Handler();
        $this->assertInstanceOf(Handler::class, $handler);
        $this->assertEquals(Handler::ENV_PRODUCTION, $handler->getEnvironment());

        $anotherHandler = new Handler(Handler::ENV_DEVELOPMENT);
        $this->assertEquals(Handler::ENV_DEVELOPMENT, $anotherHandler->getEnvironment());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::errorHandler()
     */
    public function testErrorHandler()
    {
        /** @var Handler|MockObject $handler */
        $handler = $this->getMockBuilder(Handler::class)
            ->setMethods(['exceptionHandler'])
            ->getMock();

        $handler->expects($this->once())
            ->method('exceptionHandler')
            ->with($this->isInstanceOf(ErrorException::class));

        $handler->errorHandler(1, 'Foo and bar!', '/lo/rem.php', 123);
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::exceptionHandler()
     */
    public function testExceptionHandler()
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
            ->willReturnCallback(function () use ($errorMessage) {
                echo $errorMessage;
            });

        /** @var Handler|MockObject $handler */
        $handler = $this->getMockBuilder(Handler::class)
            ->setMethods(['terminateApplicationImmediately'])
            ->getMock();
        $handler->expects($this->once())
            ->method('terminateApplicationImmediately');

        $handler->setHandler(Handler::ENV_PRODUCTION, $handlerMock);

        $this->expectOutputString($errorMessage);
        $handler->exceptionHandler($exception);

        $return = $handler->exceptionHandler($exception, true);
        $this->assertEquals($errorMessage, $return);
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::register()
     */
    public function testRegister()
    {
        /** @var Handler|MockObject $handler */
        $handler = $this->getMockForAbstractClass(Handler::class);
        $handler->register();

        set_error_handler($errorHandler = set_error_handler('var_dump'));
        $this->assertEquals($handler, array_shift($errorHandler));

        set_exception_handler($exceptionHandler = set_error_handler('var_dump'));
        $this->assertEquals($handler, array_shift($exceptionHandler));

        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getEnvironment()
     * @covers \Engelsystem\Exceptions\Handler::setEnvironment()
     */
    public function testEnvironment()
    {
        $handler = new Handler();

        $handler->setEnvironment(Handler::ENV_DEVELOPMENT);
        $this->assertEquals(Handler::ENV_DEVELOPMENT, $handler->getEnvironment());

        $handler->setEnvironment(Handler::ENV_PRODUCTION);
        $this->assertEquals(Handler::ENV_PRODUCTION, $handler->getEnvironment());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getHandler()
     * @covers \Engelsystem\Exceptions\Handler::setHandler()
     */
    public function testHandler()
    {
        $handler = new Handler();
        /** @var HandlerInterface|MockObject $devHandler */
        $devHandler = $this->getMockForAbstractClass(HandlerInterface::class);
        /** @var HandlerInterface|MockObject $prodHandler */
        $prodHandler = $this->getMockForAbstractClass(HandlerInterface::class);

        $handler->setHandler(Handler::ENV_DEVELOPMENT, $devHandler);
        $handler->setHandler(Handler::ENV_PRODUCTION, $prodHandler);
        $this->assertEquals($devHandler, $handler->getHandler(Handler::ENV_DEVELOPMENT));
        $this->assertEquals($prodHandler, $handler->getHandler(Handler::ENV_PRODUCTION));
        $this->assertCount(2, $handler->getHandler());
    }

    /**
     * @covers \Engelsystem\Exceptions\Handler::getRequest()
     * @covers \Engelsystem\Exceptions\Handler::setRequest()
     */
    public function testRequest()
    {
        $handler = new Handler();
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);

        $handler->setRequest($request);
        $this->assertEquals($request, $handler->getRequest());
    }
}
