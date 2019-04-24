<?php

namespace Engelsystem\Test\Feature\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use PHPUnit\Framework\MockObject\MockObject;

class DatabaseServiceProviderTest extends DatabaseTest
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        /** @var Application|MockObject $app */
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
