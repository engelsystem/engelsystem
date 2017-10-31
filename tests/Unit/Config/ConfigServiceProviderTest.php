<?php

namespace Engelsystem\Test\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class ConfigServiceProviderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['make', 'instance', 'get'])
            ->getMock();
        Application::setInstance($app);

        $app->expects($this->once())
            ->method('make')
            ->with(Config::class)
            ->willReturn($config);

        $app->expects($this->once())
            ->method('instance')
            ->with('config', $config);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->with('path.config')
            ->willReturn(__DIR__ . '/../../../config');

        $config->expects($this->exactly(2))
            ->method('set')
            ->withAnyParameters();

        $config->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn([]);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
    }
}
