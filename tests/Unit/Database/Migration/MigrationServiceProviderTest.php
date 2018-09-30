<?php

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Database\Database;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MigrationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Database\Migration\MigrationServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var MockObject|Migrate $migration */
        $migration = $this->getMockBuilder(Migrate::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Database|MockObject $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Connection $dbConnection */
        $dbConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SchemaBuilder $schemaBuilder */
        $schemaBuilder = $this->getMockBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'bind', 'get']);

        $app->expects($this->atLeastOnce())
            ->method('instance')
            ->withConsecutive(['db.schema'], ['db.migration'])
            ->willReturnOnConsecutiveCalls($schemaBuilder, $migration);

        $this->setExpects($app, 'bind', [SchemaBuilder::class, 'db.schema']);
        $this->setExpects($app, 'make', [Migrate::class], $migration);
        $this->setExpects($app, 'get', [Database::class], $database);

        $this->setExpects($dbConnection, 'getSchemaBuilder', null, $schemaBuilder);
        $this->setExpects($database, 'getConnection', null, $dbConnection);

        $serviceProvider = new MigrationServiceProvider($app);
        $serviceProvider->register();
    }
}
