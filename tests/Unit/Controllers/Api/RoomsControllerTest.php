<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\RoomsController;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room;

class RoomsControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\RoomsController::index
     */
    public function testIndex(): void
    {
        $this->initDatabase();
        Room::factory(3)->create();

        $controller = new RoomsController(new Response(), $this->url);

        $response = $controller->index();
        $this->validateApiResponse('/rooms', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
    }
}
