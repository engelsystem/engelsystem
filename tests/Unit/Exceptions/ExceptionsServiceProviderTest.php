<?php

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Exceptions\BasicHandler as ExceptionHandler;
use Engelsystem\Exceptions\ExceptionsServiceProvider;
use Engelsystem\Exceptions\Handler;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;

class ExceptionsServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'bind']);

        $this->setExpects($app, 'make', [ExceptionHandler::class], $exceptionHandler);
        $this->setExpects($app, 'instance', ['error.handler', $exceptionHandler]);
        $this->setExpects($app, 'bind', [Handler::class, 'error.handler']);

        $serviceProvider = new ExceptionsServiceProvider($app);
        $serviceProvider->register();
    }
}
