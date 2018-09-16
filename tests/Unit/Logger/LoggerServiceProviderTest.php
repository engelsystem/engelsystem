<?php

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Logger\EngelsystemLogger;
use Engelsystem\Logger\LoggerServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;

class LoggerServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Logger\LoggerServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|EngelsystemLogger $logger */
        $logger = $this->getMockBuilder(EngelsystemLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'bind']);

        $this->setExpects($app, 'make', [EngelsystemLogger::class], $logger);
        $this->setExpects($app, 'instance', ['logger', $logger]);

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
