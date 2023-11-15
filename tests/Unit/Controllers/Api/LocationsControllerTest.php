<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\LocationsController;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;

class LocationsControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\LocationsController::index
     * @covers \Engelsystem\Controllers\Api\Resources\LocationResource::toArray
     */
    public function testIndex(): void
    {
        $items = Location::factory(3)->create();

        $controller = new LocationsController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/locations', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('name');
        }));
    }
}
