<?php

namespace Engelsystem\Test\Feature\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DatabaseServiceProviderTest extends DatabaseTest
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        /** @var MockObject|Application $app */
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
}
