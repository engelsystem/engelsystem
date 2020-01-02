<?php

namespace Engelsystem\Test\Feature\Model;

use Engelsystem\Test\Feature\ApplicationFeatureTest;

class RoomModelTest extends ApplicationFeatureTest
{
    /** @var int */
    private $room_id = null;

    /**
     * @covers \Room_create
     */
    public function createRoom()
    {
        $this->room_id = Room_create('test', null, null);
    }

    /**
     * @covers \Room
     */
    public function testRoom()
    {
        $this->createRoom();

        $room = Room($this->room_id);

        $this->assertNotEmpty($room);
        $this->assertNotNull($room);
        $this->assertEquals($room['Name'], 'test');

        $this->assertEmpty(Room(-1));
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        if ($this->room_id != null) {
            Room_delete($this->room_id);
        }
    }
}
