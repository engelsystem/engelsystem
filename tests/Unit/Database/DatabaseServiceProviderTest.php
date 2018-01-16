<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Config\Config;
use Engelsystem\Database\DatabaseServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection;
use PDOException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DatabaseServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister()
    {
        list($app, $dbManager) = $this->prepare(['driver' => 'sqlite', 'database' => ':memory:']);

        $this->setExpects($app, 'instance', ['db', $dbManager]);

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     * @covers \Engelsystem\Database\DatabaseServiceProvider::exitOnError()
     */
    public function testRegisterError()
    {
        list($app) = $this->prepare([
            'host'     => 'localhost',
            'database' => 'database',
            'username' => 'user',
            'password' => 'password',
        ], true);

        $this->expectException(Exception::class);

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * Prepare some mocks
     *
     * @param array $dbConfigData
     * @param bool  $getPdoThrowException
     * @return array
     */
    protected function prepare($dbConfigData, $getPdoThrowException = false)
    {
        /** @var MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();
        /** @var MockObject|CapsuleManager $config */
        $dbManager = $this->getMockBuilder(CapsuleManager::class)
            ->getMock();
        /** @var MockObject|Connection $connection */
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app = $this->getApp(['get', 'make', 'instance']);

        $this->setExpects($app, 'get', ['config'], $config);
        $this->setExpects($app, 'make', [CapsuleManager::class], $dbManager);
        $this->setExpects($config, 'get', ['database'], $dbConfigData, $this->atLeastOnce());

        $this->setExpects($dbManager, 'setAsGlobal');
        $this->setExpects($dbManager, 'bootEloquent');

        $this->setExpects($connection, 'useDefaultSchemaGrammar');
        $connection->expects($this->once())
            ->method('getPdo')
            ->willReturnCallback(function () use ($getPdoThrowException) {
                if ($getPdoThrowException) {
                    throw new PDOException();
                }

                return '';
            });
        $this->setExpects($dbManager, 'getConnection', [], $connection, $this->atLeastOnce());

        return [$app, $dbManager];
    }
}
