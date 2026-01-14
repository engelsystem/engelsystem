<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\UsesAuth;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\Api\Stub\UsesAuthImplementation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UsesAuth::class, 'getUser')]
#[CoversMethod(UsesAuth::class, 'setAuth')]
#[AllowMockObjectsWithoutExpectations]
class UsesAuthTest extends ApiBaseControllerTestCase
{
    public function testGetUserNoAuthNotFound(): void
    {
        $usesAuth = $this->createInstance();

        $this->expectException(ModelNotFoundException::class);
        $usesAuth->user('self');
    }

    public function testGetUserNotFound(): void
    {
        $usesAuth = $this->createInstance();

        $this->expectException(ModelNotFoundException::class);
        $usesAuth->user(42);
    }

    public function testGetUserWithoutAuth(): void
    {
        $user = User::factory()->create();

        $usesAuth = $this->createInstance();

        $this->assertInstanceOf(User::class, $usesAuth->user($user->id));
    }

    public function testGetUser(): void
    {
        $user = User::factory()->create();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);

        $usesAuth = $this->createInstance();
        $usesAuth->setAuth($auth);

        $this->assertEquals($user, $usesAuth->user('self'));
    }

    protected function createInstance(): object
    {
        return new UsesAuthImplementation(new Response());
    }
}
