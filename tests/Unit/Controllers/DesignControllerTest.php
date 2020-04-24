<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\DesignController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DesignControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\DesignController::__construct
     * @covers \Engelsystem\Controllers\DesignController::index
     */
    public function testIndex()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->with('pages/design')
            ->willReturn($response);

        $controller = new DesignController($response);
        $return = $controller->index();

        $this->assertEquals($response, $return);
    }
}
