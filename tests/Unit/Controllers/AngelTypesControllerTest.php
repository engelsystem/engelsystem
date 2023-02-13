<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\AngelTypesController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AngelTypesControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\AngelTypesController::__construct
     * @covers \Engelsystem\Controllers\AngelTypesController::about
     */
    public function testIndex(): void
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);

        $this->setExpects(
            $response,
            'withView',
            ['pages/angeltypes/about']
        );

        $controller = new AngelTypesController($response);
        $controller->about();
    }
}
