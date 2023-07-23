<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\AngelTypeController;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypeControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::index
     */
    public function testIndex(): void
    {
        $this->initDatabase();
        AngelType::factory(3)->create();

        $controller = new AngelTypeController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/angeltypes', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
    }
}
