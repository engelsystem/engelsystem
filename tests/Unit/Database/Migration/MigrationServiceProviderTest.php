<?php

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Database\Db;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Database\Migration\MigrationServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
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
        /** @var MockObject|CapsuleManager $dbManager */
        $dbManager = $this->getMockBuilder(CapsuleManager::class)
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

        $app = $this->getApp(['make', 'instance', 'bind']);

        $app->expects($this->atLeastOnce())
            ->method('instance')
            ->withConsecutive(['db.scheme'], ['db.migration'])
            ->willReturnOnConsecutiveCalls($schemaBuilder, $migration);

        $this->setExpects($app, 'bind', [SchemaBuilder::class, 'db.scheme']);
        $this->setExpects($app, 'make', [Migrate::class], $migration);

        $this->setExpects($dbConnection, 'getSchemaBuilder', null, $schemaBuilder);
        $this->setExpects($dbManager, 'getConnection', null, $dbConnection);
        Db::setDbManager($dbManager);

        $serviceProvider = new MigrationServiceProvider($app);
        $serviceProvider->register();
    }
}
