<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Database\Database;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /** @var DatabaseConnection */
    protected $connection;

    /**
     * @covers \Engelsystem\Database\Database::__construct()
     * @covers \Engelsystem\Database\Database::getConnection()
     * @covers \Engelsystem\Database\Database::getPdo()
     */
    public function testInit()
    {
        /** @var Pdo|MockObject $pdo */
        $pdo = $this->getMockBuilder(Pdo::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DatabaseConnection|MockObject $databaseConnection */
        $databaseConnection = $this->getMockBuilder(DatabaseConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $databaseConnection->expects($this->atLeastOnce())
            ->method('getPdo')
            ->willReturn($pdo);

        $db = new Database($databaseConnection);

        $this->assertEquals($databaseConnection, $db->getConnection());
        $this->assertEquals($pdo, $db->getPdo());
        $this->assertInstanceOf(PDO::class, $db->getPdo());
    }

    /**
     * @covers \Engelsystem\Database\Database::select()
     */
    public function testSelect()
    {
        $db = new Database($this->connection);

        $return = $db->select('SELECT * FROM test_data');
        $this->assertTrue(count($return) > 3);

        $return = $db->select('SELECT * FROM test_data WHERE id = ?', [2]);
        $this->assertCount(1, $return);
    }

    /**
     * @covers \Engelsystem\Database\Database::selectOne()
     */
    public function testSelectOne()
    {
        $db = new Database($this->connection);

        $return = $db->selectOne('SELECT * FROM test_data');
        $this->assertEquals('Foo', $return->data);

        $return = $db->selectOne('SELECT * FROM test_data WHERE id = -1');
        $this->assertEmpty($return);

        $return = $db->selectOne('SELECT * FROM test_data WHERE id = ?', [3]);
        $this->assertIsNotArray($return);
    }

    /**
     * @covers \Engelsystem\Database\Database::insert()
     */
    public function testInsert()
    {
        $db = new Database($this->connection);

        $result = $db->insert("INSERT INTO test_data (id, data) VALUES (5, 'Some random text'), (6, 'another text')");
        $this->assertTrue($result);
    }

    /**
     * @covers \Engelsystem\Database\Database::update()
     */
    public function testUpdate()
    {
        $db = new Database($this->connection);

        $count = $db->update("UPDATE test_data SET data='NOPE' WHERE data LIKE '%Replaceme%'");
        $this->assertEquals(3, $count);

        $count = $db->update("UPDATE test_data SET data=? WHERE data LIKE '%NOPE%'", ['Some random text!']);
        $this->assertEquals(3, $count);
    }

    /**
     * @covers \Engelsystem\Database\Database::delete()
     */
    public function testDelete()
    {
        $db = new Database($this->connection);

        $count = $db->delete('DELETE FROM test_data WHERE id=1');
        $this->assertEquals(1, $count);

        $count = $db->delete('DELETE FROM test_data WHERE data LIKE ?', ['%Replaceme%']);
        $this->assertEquals(3, $count);
    }

    /**
     * Setup in memory database
     */
    protected function setUp(): void
    {
        $dbManager = new CapsuleManager();
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);

        $connection = $dbManager->getConnection();
        $this->connection = $connection;

        $connection->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->statement(
            '
            CREATE TABLE test_data(
                id INT PRIMARY KEY NOT NULL,
                data TEXT NOT NULL
            );
            '
        );
        $connection->statement('CREATE UNIQUE INDEX test_data_id_uindex ON test_data (id);');
        $connection->insert("
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
