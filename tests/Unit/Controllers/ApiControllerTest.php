<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\ApiController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Response;
use PHPUnit\Framework\TestCase;

class ApiControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\ApiController::__construct
     * @covers \Engelsystem\Controllers\ApiController::index
     */
    public function testIndex()
    {
        $auth = $this->createMock(Authenticator::class);
        $response = new Response();
        $controller = new ApiController($response, $auth);

        $response = $controller->index();

        $this->assertEquals(501, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());
    }
}
