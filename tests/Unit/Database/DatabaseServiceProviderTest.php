<?php

namespace Engelsystem\Test\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class DatabaseServiceProviderTest extends TestCase
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     * @covers \Engelsystem\Database\DatabaseServiceProvider::exitOnError()
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

        $app->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $config->expects($this->atLeastOnce())
            ->method('get')
            ->with('database')
            ->willReturn([
                'host' => 'localhost',
                'db'   => 'database',
                'user' => 'user',
                'pw'   => 'password',
            ]);

        $serviceProvider = new DatabaseServiceProvider($app);
        $this->expectException(Exception::class);

        $serviceProvider->register();
    }
}
