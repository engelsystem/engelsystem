<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\ApiController;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use League\OpenAPIValidation\PSR7\OperationAddress as OpenApiAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator as OpenApiResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder as OpenApiValidatorBuilder;

class ApiControllerTest extends ControllerTest
{
    protected OpenApiResponseValidator $validator;

    /**
     * @covers \Engelsystem\Controllers\ApiController::__construct
     * @covers \Engelsystem\Controllers\ApiController::index
     */
    public function testIndex(): void
    {
        $controller = new ApiController(new Response());

        $response = $controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertEquals(['*'], $response->getHeader('Access-Control-Allow-Origin'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('versions', $data);
    }

    /**
     * @covers \Engelsystem\Controllers\ApiController::indexV0
     */
    public function testIndexV0(): void
    {
        $controller = new ApiController(new Response());

        $response = $controller->indexV0();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('paths', $data);
    }

    /**
     * @covers \Engelsystem\Controllers\ApiController::options
     */
    public function testOptions(): void
    {
        $controller = new ApiController(new Response());

        $response = $controller->options();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNotEmpty($response->getHeader('Allow'));
        $this->assertNotEmpty($response->getHeader('Access-Control-Allow-Headers'));
    }

    /**
     * @covers \Engelsystem\Controllers\ApiController::notImplemented
     */
    public function testNotImplemented(): void
    {
        $controller = new ApiController(new Response());

        $response = $controller->notImplemented();

        $this->assertEquals(501, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());
    }

    /**
     * @covers \Engelsystem\Controllers\ApiController::news
     */
    public function testNews(): void
    {
        $this->initDatabase();
        News::factory(3)->create();

        $controller = new ApiController(new Response());

        $response = $controller->news();

        $operation = new OpenApiAddress('/news', 'get');
        $this->validator->validate($operation, $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(3, $data);
    }

    public function setUp(): void
    {
        parent::setUp();

        $openApiDefinition = $this->app->get('path.resources.api') . '/openapi.yml';
        $this->validator = (new OpenApiValidatorBuilder())
            ->fromYamlFile($openApiDefinition)
            ->getResponseValidator();
    }
}
