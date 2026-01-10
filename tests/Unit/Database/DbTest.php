<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Database\Db;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Db::class, 'connection')]
#[CoversMethod(Db::class, 'setDbManager')]
#[CoversMethod(Db::class, 'select')]
#[CoversMethod(Db::class, 'selectOne')]
#[CoversMethod(Db::class, 'insert')]
#[CoversMethod(Db::class, 'update')]
#[CoversMethod(Db::class, 'delete')]
#[CoversMethod(Db::class, 'getPdo')]
#[AllowMockObjectsWithoutExpectations]
class DbTest extends TestCase
{
    public function testSetDbManager(): void
    {
        $pdo = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbManager = $this->getMockBuilder(CapsuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $databaseConnection = $this->getMockBuilder(DatabaseConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dbManager
            ->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($databaseConnection);
        $databaseConnection
            ->expects($this->atLeastOnce())
            ->method('getPdo')
            ->willReturn($pdo);

        Db::setDbManager($dbManager);
        $this->assertEquals($pdo, Db::getPdo());
        $this->assertEquals($databaseConnection, Db::connection());
    }

    public function testSelect(): void
    {
        $return = Db::select('SELECT * FROM test_data');
        $this->assertTrue(count($return) > 3);

        $return = Db::select('SELECT * FROM test_data WHERE id = ?', [2]);
        $this->assertCount(1, $return);
    }

    public function testSelectOne(): void
    {
        $return = Db::selectOne('SELECT * FROM test_data');
        $this->assertEquals('Foo', $return['data']);

        $return = Db::selectOne('SELECT * FROM test_data WHERE id = -1');
        $this->assertEmpty($return);

        $return = Db::selectOne('SELECT * FROM test_data WHERE id = ?', [3]);
        $return = array_pop($return);
        $this->assertIsNotArray($return);
    }

    public function testInsert(): void
    {
        $result = Db::insert("INSERT INTO test_data (id, data) VALUES (5, 'Some random text'), (6, 'another text')");
        $this->assertTrue($result);
    }

    public function testUpdate(): void
    {
        $count = Db::update("UPDATE test_data SET data='NOPE' WHERE data LIKE '%Replaceme%'");
        $this->assertEquals(3, $count);

        $count = Db::update("UPDATE test_data SET data=? WHERE data LIKE '%NOPE%'", ['Some random text!']);
        $this->assertEquals(3, $count);
    }

    public function testDelete(): void
    {
        $count = Db::delete('DELETE FROM test_data WHERE id=1');
        $this->assertEquals(1, $count);

        $count = Db::delete('DELETE FROM test_data WHERE data LIKE ?', ['%Replaceme%']);
        $this->assertEquals(3, $count);
    }

    public function testGetPdo(): void
    {
        $pdo = Db::getPdo();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    /**
     * Setup in memory database
     */
    protected function setUp(): void
    {
        $dbManager = new CapsuleManager();
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $dbManager->setAsGlobal();
        $dbManager->bootEloquent();

        Db::setDbManager($dbManager);
        Db::getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Db::connection()->statement(
            '
            CREATE TABLE test_data(
                id INT PRIMARY KEY NOT NULL,
                data TEXT NOT NULL
            );
            '
        );
        Db::connection()->statement('CREATE UNIQUE INDEX test_data_id_uindex ON test_data (id);');
        Db::insert("
            INSERT INTO test_data (id, data)
                VALUES
                    (1, 'Foo'),
                    (2, 'Bar'),
                    (3, 'Batz'),
                    (4, 'Lorem ipsum dolor sit'),
                    (10, 'Replaceme ipsum dolor sit amet'),
                    (11, 'Lorem Replaceme dolor sit amet'),
                    (12, 'Lorem ipsum Replaceme sit amet')
            ;");
    }
}
