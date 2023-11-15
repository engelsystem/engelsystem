<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\AngelTypeController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;

class AngelTypeControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::index
     */
    public function testIndex(): void
    {
        $this->initDatabase();
        $items = AngelType::factory(3)->create();

        $controller = new AngelTypeController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/angeltypes', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('name');
        }));
    }
    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::ofUser
     * @covers \Engelsystem\Controllers\Api\Resources\UserAngelTypeResource::toArray
     */
    public function testOfUser(): void
    {
        $this->initDatabase();
        $user = User::factory()->create();
        $items = UserAngelType::factory(3)->create(['user_id' => $user->id]);

        $controller = new AngelTypeController(new Response());

        $response = $controller->ofUser(new Request([], [], ['user_id' => $user->id]));
        $this->validateApiResponse('/users/{id}/angeltypes', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->angelType->name;
        }));
    }
}
