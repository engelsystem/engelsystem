<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Database\Database;
use Engelsystem\Database\DatabaseServiceProvider;
use Engelsystem\Database\Db;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(DatabaseServiceProvider::class, 'register')]
#[CoversMethod(DatabaseServiceProvider::class, 'exitOnError')]
class DatabaseServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        /** @var Application&MockObject $app */
        /** @var CapsuleManager&MockObject $dbManager */
        /** @var PDO&MockObject $pdo */
        /** @var Database&MockObject $database */
        /** @var Connection&MockObject $connection */
        list($app, $dbManager, $pdo, $database, $connection) = $this->prepare(
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        );

        $matcher = $this->exactly(8);
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use (
                $matcher,
                $pdo,
                $dbManager,
                $connection,
                $database
            ): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(PDO::class, $parameters[0]);
                    $this->assertSame($pdo, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(CapsuleManager::class, $parameters[0]);
                    $this->assertSame($dbManager, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(Db::class, $parameters[0]);
                    $this->assertSame($dbManager, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame(Connection::class, $parameters[0]);
                    $this->assertSame($connection, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame(Database::class, $parameters[0]);
                    $this->assertEquals($database, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('db', $parameters[0]);
                    $this->assertSame($database, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 7) {
                    $this->assertSame('db.pdo', $parameters[0]);
                    $this->assertSame($pdo, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 8) {
                    $this->assertSame('db.connection', $parameters[0]);
                    $this->assertSame($connection, $parameters[1]);
                }
            });

        $serviceProvider = new DatabaseServiceProvider($app);
        $serviceProvider->register();
    }

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
        $config = $this->getMockBuilder(Config::class)
            ->getMock();
        $dbManager = $this->getMockBuilder(CapsuleManager::class)
            ->getMock();
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo = $this->getStubBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getStub();
        $database = $this->getStubBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getStub();

        $app = $this->getAppMock(['get', 'make', 'instance']);

        $this->setExpects($app, 'get', ['config'], $config);
        $matcher = $this->exactly(2);
        $config->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($dbConfigData, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('timezone', $parameters[0]);
                    return 'UTC';
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('database', $parameters[0]);
                    return $dbConfigData;
                }
            });

        $matcher = $this->atLeastOnce();
        $app->expects($matcher)
            ->method('make')->willReturnCallback(function (...$parameters) use ($database, $matcher, $dbManager) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(CapsuleManager::class, $parameters[0]);
                    return $dbManager;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(Database::class, $parameters[0]);
                    return $database;
                }
            });

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
