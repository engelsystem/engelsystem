<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\LocationsController;
use Engelsystem\Http\Redirector;
use Engelsystem\Models\Location;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

//use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[CoversMethod(LocationsController::class, '__construct')]
#[CoversMethod(LocationsController::class, 'index')]
//#[AllowMockObjectsWithoutExpectations]
class LocationsControllerTest extends ControllerTestCase
{
    protected Redirector|MockObject $redirect;

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
