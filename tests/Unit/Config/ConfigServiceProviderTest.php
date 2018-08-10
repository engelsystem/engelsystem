<?php

namespace Engelsystem\Test\Unit\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
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
        $config->expects($this->exactly(3))
            ->method('get')
            ->with(null)
            ->willReturnOnConsecutiveCalls([], [], ['lor' => 'em']);

        $configFile = __DIR__ . '/../../../config/config.php';
        $configExists = file_exists($configFile);
        if (!$configExists) {
            file_put_contents($configFile, '<?php return ["lor"=>"em"];');
        }

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();

        if (!$configExists) {
            unlink($configFile);
        }
    }

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register()
     */
    public function testRegisterException()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $this->setExpects($app, 'instance', ['config', $config]);
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/not_existing', $this->atLeastOnce());

        $this->setExpects($config, 'set', null, null, $this->never());
        $this->setExpects($config, 'get', [null], []);

        $this->expectException(Exception::class);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
    }
}
