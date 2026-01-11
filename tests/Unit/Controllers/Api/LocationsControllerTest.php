<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\LocationsController;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(LocationsController::class, 'index')]
#[CoversMethod(LocationResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class LocationsControllerTest extends ApiBaseControllerTestCase
{
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
            return $item['name'] == $items->first()->getAttribute('name')
                && $item['description'] == $items->first()->getAttribute('description')
                && $item['map_url'] == $items->first()->getAttribute('map_url')
                && $item['contact'] == ['dect' => $items->first()->getAttribute('dect')];
        }));
    }
}
