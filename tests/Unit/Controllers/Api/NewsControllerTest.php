<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\NewsController;
use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Engelsystem\Models\User\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NewsController::class, 'index')]
#[CoversMethod(NewsController::class, 'hasPermission')]
#[CoversMethod(NewsController::class, 'webPermission')]
#[CoversMethod(NewsResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class NewsControllerTest extends ApiBaseControllerTestCase
{
    public function testIndex(): void
    {
        $items = News::factory(3)->create();

        $controller = new NewsController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/news', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);

        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('title');
        }));
    }

    public function testHasPermissionWithNewsPrivilege(): void
    {
        $controller = new NewsController(new Response());
        $controller->setAuth($this->auth(['news']));

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithApiPrivilege(): void
    {
        $controller = new NewsController(new Response());
        $controller->setAuth($this->auth(['api']));

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutPrivilege(): void
    {
        $controller = new NewsController(new Response());
        $controller->setAuth($this->auth(['user_shifts']));

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutAuthenticatedUser(): void
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null);

        $controller = new NewsController(new Response());
        $controller->setAuth($auth);

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutAuthSet(): void
    {
        $controller = new NewsController(new Response());

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
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
