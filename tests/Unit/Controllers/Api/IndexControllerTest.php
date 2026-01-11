<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\Api\IndexController;
use Engelsystem\Http\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(IndexController::class, 'index')]
#[CoversMethod(IndexController::class, 'indexV0')]
#[CoversMethod(IndexController::class, 'getApiSpecV0')]
#[CoversMethod(IndexController::class, 'openApiV0')]
#[CoversMethod(IndexController::class, 'info')]
#[CoversMethod(IndexController::class, 'options')]
#[CoversMethod(IndexController::class, 'notFound')]
#[CoversMethod(IndexController::class, 'notImplemented')]
#[AllowMockObjectsWithoutExpectations]
class IndexControllerTest extends ApiBaseControllerTestCase
{
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

    public function testOpenApiV0(): void
    {
        $controller = new IndexController(new Response());

        $response = $controller->openApiV0();
        $this->validateApiResponse('/openapi', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('openapi', $data);
        $this->assertArrayHasKey('info', $data);
    }

    public function testInfo(): void
    {
        $config = new Config(['name' => 'TestEvent', 'app_name' => 'TestSystem', 'timezone' => 'UTC']);
        $this->app->instance('config', $config);

        $controller = new IndexController(new Response());

        $response = $controller->info();
        $this->validateApiResponse('/info', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $data = $data['data'];
        $this->assertArrayHasKey('api', $data);
        $this->assertArrayHasKey('timezone', $data);
        $this->assertEquals('UTC', $data['timezone']);
    }

    public function testInfoNotConfigured(): void
    {
        $config = new Config([]);
        $this->app->instance('config', $config);

        $controller = new IndexController(new Response());

        $response = $controller->info();
        $this->validateApiResponse('/info', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertEquals('', $data['data']['name']);
    }

    public function testOptions(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->options();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getHeader('allow'));
        $this->assertNotEmpty($response->getHeader('access-control-allow-headers'));
    }

    public function testNotFound(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->notFound();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());
    }

    public function testNotImplemented(): void
    {
        $controller = new IndexController(new Response());
        $response = $controller->notImplemented();

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals(['GET'], $response->getHeader('allow'));
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());
    }
}
