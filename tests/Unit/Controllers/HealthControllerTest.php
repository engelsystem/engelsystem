<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\HealthController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class HealthControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\HealthController::__construct
     * @covers \Engelsystem\Controllers\HealthController::index
     */
    public function testIndex(): void
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $this->setExpects($response, 'withContent', ['Ok'], $response);

        $controller = new HealthController($response);
        $controller->index();
    }
}
