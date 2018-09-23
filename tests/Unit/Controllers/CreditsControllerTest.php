<?php

namespace Unit\Controllers;

use Engelsystem\Controllers\CreditsController;
use Engelsystem\Http\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreditsControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\CreditsController::__construct
     * @covers \Engelsystem\Controllers\CreditsController::index
     */
    public function testIndex()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/credits.twig');

        $controller = new CreditsController($response);
        $controller->index();
    }
}
