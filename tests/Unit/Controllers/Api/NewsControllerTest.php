<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\NewsController;
use Engelsystem\Controllers\Api\Resources\NewsResource;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NewsController::class, 'index')]
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
}
