<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\ApiController;
use Engelsystem\Http\Response;

class ApiControllerTest extends ApiBaseControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Api\ApiController::__construct
     */
    public function testConstruct(): void
    {
        $controller = new class (new Response('{"some":"json"}')) extends ApiController {
            public function getResponse(): Response
            {
                return $this->response;
            }
        };

        $response = $controller->getResponse();

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $this->assertEquals(['*'], $response->getHeader('access-control-allow-origin'));
        $this->assertJson($response->getContent());
    }
}
