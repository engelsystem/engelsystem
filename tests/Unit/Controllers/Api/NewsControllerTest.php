<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\NewsController;
use Engelsystem\Controllers\Api\Resources\NewsCommentResource;
use Engelsystem\Controllers\Api\Resources\NewsDetailResource;
use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NewsController::class, 'index')]
#[CoversMethod(NewsController::class, 'show')]
#[CoversMethod(NewsController::class, 'hasPermission')]
#[CoversMethod(NewsController::class, 'webPermission')]
#[CoversMethod(NewsResource::class, 'toArray')]
#[CoversMethod(NewsDetailResource::class, 'toArray')]
#[CoversMethod(NewsCommentResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class NewsControllerTest extends ApiBaseControllerTestCase
{
    public function testIndex(): void
    {
        $items = News::factory(3)->create();
        $user = User::factory()->create();
        NewsComment::factory()->create(['news_id' => $items->first()->id, 'user_id' => $user->id]);

        $controller = new NewsController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/news', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);

        $commented = collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('title');
        });
        $this->assertCount(1, $commented);
        $this->assertEquals(1, $commented->first()['comments_count']);
    }

    public function testShow(): void
    {
        $news = News::factory()->create();
        $user = User::factory()->create();
        $comment = NewsComment::factory()->create(['news_id' => $news->id, 'user_id' => $user->id]);

        $request = new Request();
        $request = $request->withAttribute('news_id', (string) $news->id);

        $controller = new NewsController(new Response());

        $response = $controller->show($request);
        $this->validateApiResponse('/news/{id}', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($news->id, $data['data']['id']);
        $this->assertEquals(1, $data['data']['comments_count']);
        $this->assertCount(1, $data['data']['comments']);
        $this->assertEquals($comment->text, $data['data']['comments'][0]['text']);
        $this->assertEquals($user->id, $data['data']['comments'][0]['user']['id']);
    }

    public function testShowCommentsOrderedByCreatedAt(): void
    {
        $news = News::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $newer = NewsComment::factory()->create(['news_id' => $news->id, 'user_id' => $userA->id]);
        $older = NewsComment::factory()->create(['news_id' => $news->id, 'user_id' => $userB->id]);

        // Backdate the second-created comment so it's actually the older one,
        // independent of real execution timing (both could land in the same second).
        NewsComment::query()->where('id', $older->id)->update(['created_at' => Carbon::now()->subHour()]);

        $request = new Request();
        $request = $request->withAttribute('news_id', (string) $news->id);

        $controller = new NewsController(new Response());

        $response = $controller->show($request);
        $data = json_decode($response->getContent(), true);

        $this->assertCount(2, $data['data']['comments']);
        $this->assertEquals($older->id, $data['data']['comments'][0]['id']);
        $this->assertEquals($newer->id, $data['data']['comments'][1]['id']);
    }

    public function testShowWithoutComments(): void
    {
        $news = News::factory()->create();

        $request = new Request();
        $request = $request->withAttribute('news_id', (string) $news->id);

        $controller = new NewsController(new Response());

        $response = $controller->show($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(0, $data['data']['comments_count']);
        $this->assertIsArray($data['data']['comments']);
        $this->assertCount(0, $data['data']['comments']);
    }

    public function testShowNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('news_id', '999999');

        $controller = new NewsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testHasPermissionWithNewsPrivilege(): void
    {
        $controller = new NewsController(new Response());
        $controller->setAuth($this->auth(['news']));

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'show'));
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
        $this->assertFalse($controller->hasPermission(new Request(), 'show'));
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
