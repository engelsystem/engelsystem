<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\LocationsController;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(LocationsController::class, 'index')]
#[CoversMethod(LocationsController::class, 'show')]
#[CoversMethod(LocationsController::class, 'hasPermission')]
#[CoversMethod(LocationResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class LocationsControllerTest extends ApiBaseControllerTestCase
{
    public function testIndex(): void
    {
        $items = Location::factory(3)->create();

        $controller = new LocationsController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/locations', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('name')
                && $item['description'] == $items->first()->getAttribute('description')
                && $item['map_url'] == $items->first()->getAttribute('map_url')
                && $item['contact'] == ['dect' => $items->first()->getAttribute('dect')];
        }));
    }

    public function testShow(): void
    {
        $location = Location::factory()->create();

        $request = new Request();
        $request = $request->withAttribute('location_id', (string) $location->id);

        $controller = new LocationsController(new Response());

        $response = $controller->show($request);
        $this->validateApiResponse('/locations/{id}', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($location->id, $data['data']['id']);
        $this->assertEquals($location->name, $data['data']['name']);
        $this->assertEquals($location->description, $data['data']['description']);
        $this->assertEquals($location->map_url, $data['data']['map_url']);
        $this->assertEquals(['dect' => $location->dect], $data['data']['contact']);
    }

    public function testShowNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('location_id', '999999');

        $controller = new LocationsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testHasPermissionWithAuthenticatedUser(): void
    {
        $user = User::factory()->create();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);

        $controller = new LocationsController(new Response());
        $controller->setAuth($auth);

        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutAuthenticatedUser(): void
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null);

        $controller = new LocationsController(new Response());
        $controller->setAuth($auth);

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }
}
