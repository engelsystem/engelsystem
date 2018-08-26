<?php

namespace Engelsystem\Test\Unit\Config;

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
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/../../../config', $this->atLeastOnce());
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );

        $this->setExpects($config, 'set', null, null, $this->exactly(2));
        $this->setExpects($config, 'get', [null], []);

        $configFile = __DIR__ . '/../../../config/config.php';
        $configExists = file_exists($configFile);
        if (!$configExists) {
            file_put_contents($configFile, '<?php return [];');
        }

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();

        if (!$configExists) {
            unlink($configFile);
        }
    }
}
