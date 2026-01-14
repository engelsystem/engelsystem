<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Database\Database;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Database::class, '__construct')]
#[CoversMethod(Database::class, 'getConnection')]
#[CoversMethod(Database::class, 'getPdo')]
#[CoversMethod(Database::class, 'select')]
#[CoversMethod(Database::class, 'selectOne')]
#[CoversMethod(Database::class, 'insert')]
#[CoversMethod(Database::class, 'update')]
#[CoversMethod(Database::class, 'delete')]
class DatabaseTest extends TestCase
{
    protected DatabaseConnection $connection;

    public function testInit(): void
    {
        $pdo = $this->getStubBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getStub();

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

    public function testSelect(): void
    {
        $db = new Database($this->connection);

        $return = $db->select('SELECT * FROM test_data');
        $this->assertTrue(count($return) > 3);

        $return = $db->select('SELECT * FROM test_data WHERE id = ?', [2]);
        $this->assertCount(1, $return);
    }

    public function testSelectOne(): void
    {
        $db = new Database($this->connection);

        $return = $db->selectOne('SELECT * FROM test_data');
        $this->assertEquals('Foo', $return->data);

        $return = $db->selectOne('SELECT * FROM test_data WHERE id = -1');
        $this->assertEmpty($return);

        $return = $db->selectOne('SELECT * FROM test_data WHERE id = ?', [3]);
        $this->assertIsNotArray($return);
    }

    public function testInsert(): void
    {
        $db = new Database($this->connection);

        $result = $db->insert("INSERT INTO test_data (id, data) VALUES (5, 'Some random text'), (6, 'another text')");
        $this->assertTrue($result);
    }

    public function testUpdate(): void
    {
        $db = new Database($this->connection);

        $count = $db->update("UPDATE test_data SET data='NOPE' WHERE data LIKE '%Replaceme%'");
        $this->assertEquals(3, $count);

        $count = $db->update("UPDATE test_data SET data=? WHERE data LIKE '%NOPE%'", ['Some random text!']);
        $this->assertEquals(3, $count);
    }

    public function testDelete(): void
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
