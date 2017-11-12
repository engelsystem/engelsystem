<?php

namespace Engelsystem\Test\Unit\Exceptions;

use Engelsystem\Exceptions\ExceptionsServiceProvider;
use Engelsystem\Exceptions\Handler as ExceptionHandler;
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

        $app = $this->getApp();

        $this->setExpects($app, 'make', [ExceptionHandler::class], $exceptionHandler);
        $this->setExpects($app, 'instance', ['error.handler', $exceptionHandler]);

        $serviceProvider = new ExceptionsServiceProvider($app);
        $serviceProvider->register();
    }
}
