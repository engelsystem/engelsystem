<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\LocationsController;
use Engelsystem\Http\Redirector;
use Engelsystem\Models\Location;
use PHPUnit\Framework\MockObject\MockObject;

class LocationsControllerTest extends ControllerTest
{
    protected Redirector|MockObject $redirect;

    /**
     * @covers \Engelsystem\Controllers\LocationsController::__construct
     * @covers \Engelsystem\Controllers\LocationsController::index
     */
    public function testIndex(): void
    {
        /** @var LocationsController $controller */
        $controller = $this->app->make(LocationsController::class);
        Location::factory(5)->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('pages/locations/index', $view);
                $this->assertTrue($data['is_index'] ?? false);
                $this->assertCount(5, $data['locations'] ?? []);
                return $this->response;
            });

        $controller->index();
    }
}
