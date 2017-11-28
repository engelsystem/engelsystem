<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;

class DatabaseServiceProviderTest extends ServiceProviderTest
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

        $app = $this->getApp(['get']);

        $this->setExpects($app, 'get', ['config'], $config);
        $this->setExpects($config, 'get', ['database'], [
            'host' => 'localhost',
            'db'   => 'database',
            'user' => 'user',
            'pw'   => 'password',
        ], $this->atLeastOnce());
        $this->expectException(Exception::class);

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }
}
