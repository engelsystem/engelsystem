<?php

namespace Engelsystem\Test\Unit\Helpers;

use Carbon\CarbonTimeZone;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\ConfigureEnvironmentServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigureEnvironmentServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\ConfigureEnvironmentServiceProvider::register
     */
    public function testRegister()
    {
        $config = new Config(['timezone' => 'Australia/Eucla']);
        $this->app->instance('config', $config);

        /** @var ConfigureEnvironmentServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(ConfigureEnvironmentServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['setTimeZone'])
            ->getMock();

        $serviceProvider->expects($this->once())
            ->method('setTimeZone')
            ->willReturnCallback(function (CarbonTimeZone $timeZone) {
                $this->assertEquals('Australia/Eucla', $timeZone->getName());
            });

        $serviceProvider->register();
    }
}
