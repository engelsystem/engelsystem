<?php

namespace Engelsystem\Test\Feature\Model;

use PHPUnit\Framework\TestCase;

class RoomModelTest extends TestCase
{
    private $room_id = null;

    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/../../../includes/engelsystem.php';
    }

    public function createRoom()
    {
        $this->room_id = Room_create('test', false, null, null);
    }

    public function testRoom()
    {
        $this->createRoom();

        $room = Room($this->room_id);

        $this->assertNotFalse($room);
        $this->assertNotNull($room);
        $this->assertEquals($room['Name'], 'test');

        $this->assertNull(Room(-1));
    }

    public function tearDown()
    {
        if ($this->room_id != null) {
            Room_delete($this->room_id);
        }
    }
}
