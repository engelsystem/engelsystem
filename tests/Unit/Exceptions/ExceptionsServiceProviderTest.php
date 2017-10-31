<?php

namespace Engelsystem\Test\Exceptions;

use Engelsystem\Application;
use Engelsystem\Exceptions\ExceptionsServiceProvider;
use Engelsystem\Exceptions\Handler as ExceptionHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class ExceptionsServiceProviderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\ExceptionsServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->getMockBuilder(ExceptionHandler::class)
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['make', 'instance'])
            ->getMock();

        $app->expects($this->once())
            ->method('make')
            ->with(ExceptionHandler::class)
            ->willReturn($exceptionHandler);

        $app->expects($this->once())
            ->method('instance')
            ->with('error.handler', $exceptionHandler);

        $serviceProvider = new ExceptionsServiceProvider($app);
        $serviceProvider->register();
    }
}
