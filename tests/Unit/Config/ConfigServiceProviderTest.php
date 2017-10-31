<?php

namespace Engelsystem\Test\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject;

class ConfigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $this->setExpects($app, 'instance', ['config', $config]);
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/../../../config', $this->atLeastOnce());

        $this->setExpects($config, 'set', null, null, $this->exactly(2));
        $this->setExpects($config, 'get', [null], []);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
    }
}
