<?php

namespace Engelsystem\Test\Unit\Http\SessionHandlers;

use Engelsystem\Http\SessionHandlers\DatabaseHandler;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\TestCase;

class DatabaseHandlerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::__construct
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::getQuery
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::read
     */
    public function testRead()
    {
        $handler = new DatabaseHandler($this->database);
        $this->assertEquals('', $handler->read('foo'));

        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', CURRENT_TIMESTAMP)");
        $this->assertEquals('Lorem Ipsum', $handler->read('foo'));
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::getCurrentTimestamp
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::write
     */
    public function testWrite()
    {
        $handler = new DatabaseHandler($this->database);

        foreach (['Lorem Ipsum', 'Dolor Sit!'] as $data) {
            $this->assertTrue($handler->write('foo', $data));

            $return = $this->database->select('SELECT * FROM sessions WHERE id = :id', ['id' => 'foo']);
            $this->assertCount(1, $return);

            $return = array_shift($return);
            $this->assertEquals($data, $return->payload);
        }
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::destroy
     */
    public function testDestroy()
    {
        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', CURRENT_TIMESTAMP)");
        $this->database->insert("INSERT INTO sessions VALUES ('bar', 'Dolor Sit', CURRENT_TIMESTAMP)");

        $handler = new DatabaseHandler($this->database);
        $this->assertTrue($handler->destroy('batz'));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(2, $return);

        $this->assertTrue($handler->destroy('bar'));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(1, $return);

        $return = array_shift($return);
        $this->assertEquals('foo', $return->id);
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\DatabaseHandler::gc
     */
    public function testGc()
    {
        $this->database->insert("INSERT INTO sessions VALUES ('foo', 'Lorem Ipsum', '2000-01-01 01:00')");
        $this->database->insert("INSERT INTO sessions VALUES ('bar', 'Dolor Sit', '3000-01-01 01:00')");

        $handler = new DatabaseHandler($this->database);

        $this->assertTrue($handler->gc(60 * 60));

        $return = $this->database->select('SELECT * FROM sessions');
        $this->assertCount(1, $return);

        $return = array_shift($return);
        $this->assertEquals('bar', $return->id);
    }

    /**
     * Prepare tests
     */
    protected function setUp(): void
    {
        $this->initDatabase();
    }
}
