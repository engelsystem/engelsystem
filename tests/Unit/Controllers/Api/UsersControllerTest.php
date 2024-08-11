<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\UsersController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use PHPUnit\Framework\MockObject\MockObject;

class UsersControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\UsersController::user
     * @covers \Engelsystem\Controllers\Api\Resources\UserDetailResource::toArray
     * @covers \Engelsystem\Controllers\Api\Resources\UserResource::toArray
     */
    public function testUser(): void
    {
        $user = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory())
            ->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new UsersController(new Response());
        $controller->setAuth($auth);

        $response = $controller->user($request);
        $this->validateApiResponse('/users/{id}', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals($user->id, $data['data']['id']);
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertEquals($user->name, $data['data']['name']);
        $this->assertArrayHasKey('email', $data['data']);
        $this->assertArrayHasKey('dates', $data['data']);
        $this->assertArrayHasKey('contact', $data['data']);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\UsersController::user
     */
    public function testUserById(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory())
            ->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', $otherUser->id);

        $controller = new UsersController(new Response());
        $controller->setAuth($auth);

        $response = $controller->user($request);
        $this->validateApiResponse('/users/{id}', 'get', $response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals($otherUser->id, $data['data']['id']);
        $this->assertArrayNotHasKey('dates', $data['data']);
    }
}
