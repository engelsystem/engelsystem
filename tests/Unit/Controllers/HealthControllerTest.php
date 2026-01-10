<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\HealthController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(HealthController::class, '__construct')]
#[CoversMethod(HealthController::class, 'index')]
class HealthControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $response = $this->createMock(Response::class);
        $this->setExpects($response, 'withContent', ['Ok'], $response);

        $controller = new HealthController($response);
        $controller->index();
    }
}
