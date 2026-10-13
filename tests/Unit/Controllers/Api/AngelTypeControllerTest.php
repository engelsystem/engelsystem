<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\AngelTypeController;
use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(AngelTypeController::class, 'index')]
#[CoversMethod(AngelTypeController::class, 'show')]
#[CoversMethod(AngelTypeController::class, 'hasPermission')]
#[CoversMethod(AngelTypeController::class, 'webPermission')]
#[CoversMethod(AngelTypeResource::class, 'toArray')]
#[CoversMethod(AngelTypeController::class, 'ofUser')]
#[CoversMethod(UserAngelTypeResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class AngelTypeControllerTest extends ApiBaseControllerTestCase
{
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
                && $item['restricted'] == $first->getAttribute('restricted')
                && $item['contact'] == [
                    'name' => $first->getAttribute('contact_name'),
                    'email' => $first->getAttribute('contact_email'),
                    'dect' => $first->getAttribute('contact_dect'),
                ];
        }));
    }

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
            return $item['angeltype']['id'] == $items->first()->angelType->id;
        }));
    }

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

    public function testEntriesByUserNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('user_id', 42);

        $controller = new AngelTypeController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->ofUser($request);
    }

    public function testShow(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create([
            'requires_driver_license' => true,
            'requires_ifsg_certificate' => false,
            'shift_self_signup' => true,
        ]);

        $request = (new Request())->withAttribute('angeltype_id', (string) $angelType->id);
        $controller = new AngelTypeController(new Response());

        $response = $controller->show($request);
        $this->validateApiResponse('/angeltypes/{id}', 'get', $response);

        $data = json_decode($response->getContent(), true)['data'];
        $this->assertEquals($angelType->id, $data['id']);
        $this->assertEquals($angelType->name, $data['name']);
        $this->assertTrue($data['requires_driver_license']);
        $this->assertFalse($data['requires_ifsg_certificate']);
        $this->assertTrue($data['shift_self_signup']);
    }

    public function testShowNotFound(): void
    {
        $request = (new Request())->withAttribute('angeltype_id', '999999');
        $controller = new AngelTypeController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testHasPermission(): void
    {
        $controller = new AngelTypeController(new Response());
        $otherUser = (new Request())->withAttribute('user_id', '42');

        $controller->setAuth($this->auth(['angeltypes.view']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'show'));
        $this->assertTrue($controller->hasPermission($otherUser, 'ofUser'));

        $controller->setAuth($this->auth([]));
        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
        $this->assertFalse($controller->hasPermission(new Request(), 'show'));
        $this->assertTrue($controller->hasPermission($otherUser, 'ofUser'));

        $controller->setAuth($this->auth(['api']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
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
}
