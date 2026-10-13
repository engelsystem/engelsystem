<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\UsesAuth;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\Api\Stub\UsesAuthImplementation;
use Engelsystem\Test\Unit\Controllers\Api\Stub\UsesAuthWebPermissionImplementation as WebPermission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UsesAuth::class, 'getUser')]
#[CoversMethod(UsesAuth::class, 'setAuth')]
#[CoversMethod(UsesAuth::class, 'hasPermission')]
#[CoversMethod(UsesAuth::class, 'webPermission')]
#[CoversMethod(UsesAuth::class, 'hasApiPrivilege')]
#[CoversMethod(UsesAuth::class, 'isReadRequest')]
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

    public function testHasPermissionWithoutAuthSet(): void
    {
        $usesAuth = $this->createInstance();

        $this->assertFalse($usesAuth->hasPermission($this->request(), 'index'));
    }

    public function testHasPermissionWithoutAuthenticatedUser(): void
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null);
        $auth->expects($this->never())->method('can');

        $usesAuth = $this->createInstance(WebPermission::class);
        $usesAuth->setAuth($auth);

        $this->assertFalse($usesAuth->hasPermission($this->request(), 'open'));
    }

    public function testHasPermissionSelfWithAuthenticatedUser(): void
    {
        $usesAuth = $this->createInstance();
        $usesAuth->setAuth($this->auth([]));

        $request = $this->request()->withAttribute('user_id', 'self');

        $this->assertTrue($usesAuth->hasPermission($request, 'worklogs'));
    }

    public function testHasPermissionApiPrivilegeGrantsReading(): void
    {
        $usesAuth = $this->createInstance();
        $usesAuth->setAuth($this->auth(['api']));

        $this->assertTrue($usesAuth->hasPermission($this->request(), 'index'));
        $this->assertTrue($usesAuth->hasPermission($this->request()->withAttribute('user_id', '42'), 'user'));
        $this->assertTrue($usesAuth->hasPermission($this->request('HEAD'), 'index'));
    }

    public function testHasPermissionApiPrivilegeDoesNotGrantWriting(): void
    {
        $usesAuth = $this->createInstance(WebPermission::class);
        $usesAuth->setAuth($this->auth(['api']));

        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->assertFalse($usesAuth->hasPermission($this->request($method), 'save'), $method);
            $this->assertFalse($usesAuth->hasPermission($this->request($method), 'index'), $method);
        }
    }

    public function testHasPermissionWithoutApiPrivilegeAndWithoutWebPermission(): void
    {
        $usesAuth = $this->createInstance();
        $usesAuth->setAuth($this->auth(['news']));

        $this->assertFalse($usesAuth->hasPermission($this->request(), 'index'));
        $this->assertFalse($usesAuth->hasPermission($this->request()->withAttribute('user_id', '42'), 'user'));
    }

    public function testHasPermissionWebPermission(): void
    {
        $usesAuth = $this->createInstance(WebPermission::class);
        $usesAuth->setAuth($this->auth(['web.read']));

        $this->assertTrue($usesAuth->hasPermission($this->request(), 'index'));
        $this->assertTrue($usesAuth->hasPermission($this->request(), 'open'));
        $this->assertFalse($usesAuth->hasPermission($this->request('POST'), 'save'));
        $this->assertFalse($usesAuth->hasPermission($this->request(), 'other'));
    }

    public function testHasPermissionWebPermissionGrantsWriting(): void
    {
        $usesAuth = $this->createInstance(WebPermission::class);
        $usesAuth->setAuth($this->auth(['web.write']));

        $this->assertTrue($usesAuth->hasPermission($this->request('POST'), 'save'));
        $this->assertFalse($usesAuth->hasPermission($this->request(), 'index'));
    }

    /**
     * @param string[] $privileges
     */
    protected function auth(array $privileges): Authenticator
    {
        $auth = $this->createMock(Authenticator::class);
        $auth->method('user')->willReturn(User::factory()->create());
        $auth->method('can')->willReturnCallback(
            fn(array|string $abilities): bool => empty(array_diff((array) $abilities, $privileges))
        );

        return $auth;
    }

    protected function request(string $method = 'GET'): Request
    {
        return (new Request())->withMethod($method);
    }

    protected function createInstance(string $class = UsesAuthImplementation::class): object
    {
        return new $class(new Response());
    }
}
