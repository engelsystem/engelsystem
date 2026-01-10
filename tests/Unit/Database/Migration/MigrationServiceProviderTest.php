<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(MigrationServiceProvider::class, 'register')]
class MigrationServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $migration = $this->getStubBuilder(Migrate::class)
            ->disableOriginalConstructor()
            ->getStub();
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaBuilder = $this->getStubBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getStub();

        $app = $this->getAppMock(['make', 'instance', 'bind', 'get']);

        $matcher = $this->atLeastOnce();
        $app->expects($matcher)
            ->method('instance')
            ->willReturnCallback(function (...$parameters) use ($migration, $schemaBuilder, $matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('db.schema', $parameters[0]);
                    return $schemaBuilder;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('db.migration', $parameters[0]);
                    return $migration;
                }
            });

        $this->setExpects($app, 'bind', [SchemaBuilder::class, 'db.schema']);
        $this->setExpects($app, 'make', [Migrate::class], $migration);
        $this->setExpects($app, 'get', [Database::class], $database);

        $this->setExpects($dbConnection, 'getSchemaBuilder', null, $schemaBuilder);
        $this->setExpects($database, 'getConnection', null, $dbConnection);

        $serviceProvider = new MigrationServiceProvider($app);
        $serviceProvider->register();
    }
}
