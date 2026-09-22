<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\NewsController;
use Engelsystem\Controllers\Api\Resources\NewsCommentResource;
use Engelsystem\Controllers\Api\Resources\NewsDetailResource;
use Engelsystem\Controllers\Api\Resources\NewsResource;
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

    public function testShowNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('news_id', '999999');

        $controller = new NewsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }
}
