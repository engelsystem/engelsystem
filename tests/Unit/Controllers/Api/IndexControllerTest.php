<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\IndexController;
use Engelsystem\Http\Response;

class IndexControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\IndexController::__construct
     * @covers \Engelsystem\Controllers\Api\IndexController::index
     */
    public function testIndex(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertEquals(['*'], $response->getHeader('access-control-allow-origin'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('versions', $data);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\IndexController::indexV0
     */
    public function testIndexV0(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->indexV0();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('paths', $data);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\IndexController::options
     */
    public function testOptions(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->options();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNotEmpty($response->getHeader('allow'));
        $this->assertNotEmpty($response->getHeader('access-control-allow-headers'));
    }

    /**
     * @covers \Engelsystem\Controllers\Api\IndexController::notImplemented
     */
    public function testNotImplemented(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->notImplemented();

        $this->assertEquals(501, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());
    }
}
