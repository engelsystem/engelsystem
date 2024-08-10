<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\AngelTypeController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AngelTypeControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::index
     * @covers \Engelsystem\Controllers\Api\Resources\AngelTypeResource::toArray
     */
    public function testIndex(): void
    {
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
            $first = $items->first();
            return $item['name'] == $first->getAttribute('name')
                && $item['description'] == $first->getAttribute('description')
                && $item['restricted'] == $first->getAttribute('restricted');
        }));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::ofUser
     * @covers \Engelsystem\Controllers\Api\Resources\UserAngelTypeResource::toArray
     */
    public function testOfUser(): void
    {
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

    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::ofUser
     */
    public function testEntriesOfUserSelf(): void
    {
        $user = User::factory()->create();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new AngelTypeController(new Response());
        $controller->setAuth($auth);

        $response = $controller->ofUser($request);
        $this->validateApiResponse('/users/{id}/angeltypes', 'get', $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\AngelTypeController::ofUser
     */
    public function testEntriesByUserNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('user_id', 42);

        $controller = new AngelTypeController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->ofUser($request);
    }
}
