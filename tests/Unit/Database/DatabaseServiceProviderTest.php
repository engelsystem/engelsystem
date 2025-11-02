<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\Database;
use Engelsystem\Database\DatabaseServiceProvider;
use Engelsystem\Database\Db;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection;
use PDO;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;

class DatabaseServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister(): void
    {
        /** @var Application|MockObject $app */
        /** @var CapsuleManager|MockObject $dbManager */
        /** @var PDO|MockObject $pdo */
        /** @var Database|MockObject $database */
        /** @var Connection|MockObject $connection */
        list($app, $dbManager, $pdo, $database, $connection) = $this->prepare(
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        );

        $app->expects($this->exactly(8))
            ->method('instance')
            ->withConsecutive(
                [PDO::class, $pdo],
                [CapsuleManager::class, $dbManager],
                [Db::class, $dbManager],
                [Connection::class, $connection],
                [Database::class, $database],
                ['db', $database],
                ['db.pdo', $pdo],
                ['db.connection', $connection]
            );

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::exitOnError()
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegisterError(): void
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
     */
    protected function prepare(array $dbConfigData, bool $getPdoThrowException = false): array
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();
        /** @var CapsuleManager|MockObject $config */
        $dbManager = $this->getMockBuilder(CapsuleManager::class)
            ->getMock();
        /** @var Connection|MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var PDO|MockObject $pdo */
        $pdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Database|MockObject $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app = $this->getApp(['get', 'make', 'instance']);

        $this->setExpects($app, 'get', ['config'], $config);
        $config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['timezone'], ['database'])
            ->willReturnOnConsecutiveCalls('UTC', $dbConfigData);

        $app->expects($this->atLeastOnce())
            ->method('make')
            ->withConsecutive(
                [CapsuleManager::class],
                [Database::class]
            )
            ->willReturn(
                $dbManager,
                $database
            );

        $this->setExpects($dbManager, 'setAsGlobal');
        $this->setExpects($dbManager, 'bootEloquent');

        $this->setExpects($connection, 'useDefaultSchemaGrammar');
        $connection->expects($this->once())
            ->method('getPdo')
            ->willReturnCallback(function () use ($getPdoThrowException, $pdo) {
                if ($getPdoThrowException) {
                    throw new PDOException();
                }

                return $pdo;
            });
        $this->setExpects($dbManager, 'getConnection', [], $connection, $this->atLeastOnce());

        return [$app, $dbManager, $pdo, $database, $connection];
    }
}
