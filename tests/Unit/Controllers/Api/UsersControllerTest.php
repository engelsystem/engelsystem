<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\UsersController;
use Engelsystem\Helpers\Authenticator;
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
     * @covers \Engelsystem\Controllers\Api\UsersController::__construct
     * @covers \Engelsystem\Controllers\Api\UsersController::self
     * @covers \Engelsystem\Controllers\Api\Resources\UserDetailResource::toArray
     */
    public function testSelf(): void
    {
        $user = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory())
            ->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);

        $controller = new UsersController(new Response(), $auth);

        $response = $controller->self();
        $this->validateApiResponse('/users/self', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals($user->id, $data['data']['id']);
        $this->assertArrayHasKey('dates', $data['data']);
    }
}
