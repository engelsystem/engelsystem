<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpUnauthorized;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\Api\Stub\OwnAuthImplementation;
use PHPUnit\Framework\MockObject\MockObject;

class OwnAuthTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testReturnsNullForUnknownMethod(): void
    {
        $controller = new OwnAuthImplementation(new Response());
        $request = (new Request())->withAttribute('user_id', 'self');

        $this->assertNull($controller->hasPermission($request, 'somethingElse'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testReturnsNullWhenAuthMissing(): void
    {
        $controller = new OwnAuthImplementation(new Response());
        $request = (new Request())->withAttribute('user_id', 42);

        $this->assertNull($controller->hasPermission($request, 'allowed'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testThrowsUnauthorizedForUnauthSelf(): void
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->once())->method('user')->willReturn(null);
        $auth->expects($this->never())->method('can');

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', 'self');

        $this->expectException(HttpUnauthorized::class);
        $controller->hasPermission($request, 'allowed');
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testReturnsNullForUnauthOtherUser(): void
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->once())->method('user')->willReturn(null);

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', 42);

        $this->assertNull($controller->hasPermission($request, 'allowed'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testReturnsNullWithoutApiOwnPrivilege(): void
    {
        $user = User::factory()->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->any())->method('user')->willReturn($user);
        $auth->expects($this->once())->method('can')->with('api.own')->willReturn(false);

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', 'self');

        $this->assertNull($controller->hasPermission($request, 'allowed'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testGrantsAccessForSelfAlias(): void
    {
        $user = User::factory()->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->any())->method('user')->willReturn($user);
        $auth->expects($this->once())->method('can')->with('api.own')->willReturn(true);

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', 'self');

        $this->assertTrue($controller->hasPermission($request, 'allowed'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testGrantsAccessForOwnId(): void
    {
        $user = User::factory()->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->any())->method('user')->willReturn($user);
        $auth->expects($this->once())->method('can')->with('api.own')->willReturn(true);

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', (string) $user->id);

        $this->assertTrue($controller->hasPermission($request, 'allowed'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\OwnAuth::hasPermission
     */
    public function testReturnsNullForOtherUserId(): void
    {
        $user = User::factory()->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->any())->method('user')->willReturn($user);
        $auth->expects($this->once())->method('can')->with('api.own')->willReturn(true);

        $controller = new OwnAuthImplementation(new Response());
        $controller->setAuth($auth);
        $request = (new Request())->withAttribute('user_id', (string) ($user->id + 1));

        $this->assertNull($controller->hasPermission($request, 'allowed'));
    }
}
