<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
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
        $config = new Config(['foo' => 'bar', 'credits' => ['lor' => 'em']]);

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/credits.twig', ['credits' => ['lor' => 'em']]);

        $controller = new CreditsController($response, $config);
        $controller->index();
    }
}
