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
        $config = new Config(['timezone' => 'Australia/Eucla', 'environment' => 'production']);
        $this->app->instance('config', $config);

        /** @var ConfigureEnvironmentServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(ConfigureEnvironmentServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['setTimeZone', 'displayErrors', 'errorReporting'])
            ->getMock();

        $serviceProvider->expects($this->exactly(2))
            ->method('setTimeZone')
            ->willReturnCallback(function (CarbonTimeZone $timeZone) {
                $this->assertEquals('Australia/Eucla', $timeZone->getName());
            });
        $serviceProvider->expects($this->exactly(3))
            ->method('displayErrors')
            ->withConsecutive([false], [false], [true]);
        $serviceProvider->expects($this->exactly(1))
            ->method('errorReporting')
            ->with(E_ALL);

        $serviceProvider->register();
        $config->set('environment', 'development');
        $serviceProvider->register();
    }
}
