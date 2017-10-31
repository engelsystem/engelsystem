<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Database\Db;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Throwable;

class DbTest extends TestCase
{
    /**
     * @covers \Engelsystem\Database\Db::connect()
     */
    public function testConnect()
    {
        $result = Db::connect('mysql:host=localhost;dbname=someTestDatabaseThatDoesNotExist;charset=utf8');
        $this->assertFalse($result);

        $result = Db::connect('sqlite::memory');
        $this->assertTrue($result);
    }

    /**
     * @covers \Engelsystem\Database\Db::query()
     */
    public function testQuery()
    {
        $stm = Db::query('SELECT * FROM test_data');
        $this->assertEquals('00000', $stm->errorCode());

        $stm = Db::query('SELECT * FROM test_data WHERE id = ?', [4]);
        $this->assertEquals('00000', $stm->errorCode());
    }

    /**
     * @covers \Engelsystem\Database\Db::unprepared()
     */
    public function testUnprepared()
    {
        $return = Db::unprepared('SELECT * FROM test_data WHERE id = 3');
        $this->assertTrue($return);
    }

    /**
     * @covers \Engelsystem\Database\Db::select()
     */
    public function testSelect()
    {
        $return = Db::select('SELECT * FROM test_data');
        $this->assertTrue(count($return) > 3);

        $return = Db::select('SELECT * FROM test_data WHERE id = ?', [2]);
        $this->assertCount(1, $return);
    }

    /**
     * @covers \Engelsystem\Database\Db::selectOne()
     */
    public function testSelectOne()
    {
        $return = Db::selectOne('SELECT * FROM test_data');
        $this->assertEquals('Foo', $return['data']);

        $return = Db::selectOne('SELECT * FROM test_data WHERE id = -1');
        $this->assertEmpty($return);

        $return = Db::selectOne('SELECT * FROM test_data WHERE id = ?', [3]);
        $return = array_pop($return);
        $this->assertTrue(!is_array($return));
    }

    /**
     * @covers \Engelsystem\Database\Db::insert()
     */
    public function testInsert()
    {
        $count = Db::insert("INSERT INTO test_data (id, data) VALUES (5, 'Some random text'), (6, 'another text')");
        $this->assertEquals(2, $count);

        $count = Db::insert('INSERT INTO test_data(id, data) VALUES (:id, :alias)', ['id' => 7, 'alias' => 'Blafoo']);
        $this->assertEquals(1, $count);
    }

    /**
     * @covers \Engelsystem\Database\Db::update()
     */
    public function testUpdate()
    {
        $count = Db::update("UPDATE test_data SET data='NOPE' WHERE data LIKE '%Replaceme%'");
        $this->assertEquals(3, $count);

        $count = Db::update("UPDATE test_data SET data=? WHERE data LIKE '%NOPE%'", ['Some random text!']);
        $this->assertEquals(3, $count);
    }

    /**
     * @covers \Engelsystem\Database\Db::delete()
     */
    public function testDelete()
    {
        $count = Db::delete('DELETE FROM test_data WHERE id=1');
        $this->assertEquals(1, $count);

        $count = Db::delete('DELETE FROM test_data WHERE data LIKE ?', ['%Replaceme%']);
        $this->assertEquals(3, $count);
    }

    /**
     * @covers \Engelsystem\Database\Db::statement()
     */
    public function testStatement()
    {
        $return = Db::statement('SELECT * FROM test_data WHERE id = 3');
        $this->assertTrue($return);

        $return = Db::statement('SELECT * FROM test_data WHERE id = ?', [2]);
        $this->assertTrue($return);
    }

    /**
     * @covers \Engelsystem\Database\Db::getError()
     */
    public function testGetError()
    {
        try {
            Db::statement('foo');
        } catch (Throwable $e) {
        }

        $error = Db::getError();
        $this->assertTrue(is_array($error));
        $this->assertEquals('near "foo": syntax error', $error[2]);

        $db = new Db();
        $refObject = new ReflectionObject($db);
        $refProperty = $refObject->getProperty('stm');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null, null);

        $error = Db::getError();
        $this->assertEquals([-1, null, null], $error);
    }

    /**
     * @covers \Engelsystem\Database\Db::getPdo()
     */
    public function testGetPdo()
    {
        $pdo = Db::getPdo();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    /**
     * @covers \Engelsystem\Database\Db::getStm()
     */
    public function testGetStm()
    {
        $stm = Db::getStm();
        $this->assertInstanceOf(PDOStatement::class, $stm);
    }

    /**
     * Setup in memory database
     */
    protected function setUp()
    {
        Db::connect('sqlite::memory:');
        Db::getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Db::query(
            '
            CREATE TABLE test_data(
                id INT PRIMARY KEY NOT NULL,
                data TEXT NOT NULL
            );
        ');
        Db::query('CREATE UNIQUE INDEX test_data_id_uindex ON test_data (id);');
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
