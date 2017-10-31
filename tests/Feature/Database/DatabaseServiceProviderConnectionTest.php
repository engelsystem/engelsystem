<?php

namespace Engelsystem\Test\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class DatabaseServiceProviderConnectionTest extends TestCase
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|Application $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['get'])
            ->getMock();
        Application::setInstance($app);

        $app->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $config->expects($this->atLeastOnce())
            ->method('get')
            ->with('database')
            ->willReturn($this->getDbConfig());

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }

    private function getDbConfig()
    {
        $configValues = require __DIR__ . '/../../../config/config.default.php';
        $configFile = __DIR__ . '/../../../config/config.php';

        if (file_exists($configFile)) {
            $configValues = array_replace_recursive($configValues, require $configFile);
        }

        return $configValues['database'];
    }
}
