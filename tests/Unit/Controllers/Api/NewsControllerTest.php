<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\NewsController;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;

class NewsControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\NewsController::index
     */
    public function testIndex(): void
    {
        News::factory(3)->create();

        $controller = new NewsController(new Response(), $this->url);

        $response = $controller->index();
        $this->validateApiResponse('/news', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
    }
}
