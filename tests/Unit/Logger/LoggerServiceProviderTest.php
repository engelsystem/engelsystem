<?php

namespace Engelsystem\Test\Logger;

use Engelsystem\Application;
use Engelsystem\Logger\EngelsystemLogger;
use Engelsystem\Logger\LoggerServiceProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;

class LoggerServiceProviderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Logger\LoggerServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|EngelsystemLogger $logger */
        $logger = $this->getMockBuilder(EngelsystemLogger::class)
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['make', 'instance', 'bind'])
            ->getMock();

        $app->expects($this->once())
            ->method('make')
            ->with(EngelsystemLogger::class)
            ->willReturn($logger);

        $app->expects($this->once())
            ->method('instance')
            ->with('logger', $logger);

        $app->expects($this->atLeastOnce())
            ->method('bind')
            ->withConsecutive(
                [LoggerInterface::class, 'logger'],
                [EngelsystemLogger::class, 'logger']
            );

        $serviceProvider = new LoggerServiceProvider($app);
        $serviceProvider->register();
    }
}
