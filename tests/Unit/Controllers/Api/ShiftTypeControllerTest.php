<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\ShiftTypeResource;
use Engelsystem\Controllers\Api\ShiftTypeController;
use Engelsystem\Http\Response;
use Engelsystem\Models\Shifts\ShiftType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ShiftTypeController::class, 'index')]
#[CoversMethod(ShiftTypeResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class ShiftTypeControllerTest extends ApiBaseControllerTestCase
{
    public function testIndex(): void
    {
        $items = ShiftType::factory(3)->create();

        $controller = new ShiftTypeController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/shifttypes', 'get', $response);

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
