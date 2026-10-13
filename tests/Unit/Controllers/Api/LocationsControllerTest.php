<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\LocationsController;
use Engelsystem\Controllers\Api\Resources\LocationResource;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(LocationsController::class, 'index')]
#[CoversMethod(LocationsController::class, 'show')]
#[CoversMethod(LocationsController::class, 'hasPermission')]
#[CoversMethod(LocationsController::class, 'webPermission')]
#[CoversMethod(LocationsController::class, 'withDetails')]
#[CoversMethod(LocationsController::class, 'locationData')]
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

    public function testShowWithoutDescription(): void
    {
        $location = Location::factory()->create(['description' => null]);

        $request = new Request();
        $request = $request->withAttribute('location_id', (string) $location->id);

        $controller = new LocationsController(new Response());

        $response = $controller->show($request);
        $data = json_decode($response->getContent(), true);

        // The resource falls back to an empty string instead of null.
        $this->assertSame('', $data['data']['description']);
    }

    public function testShowNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('location_id', '999999');

        $controller = new LocationsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testShowWithoutDetailPrivileges(): void
    {
        $location = Location::factory()->create();
        NeededAngelType::factory()->create(['location_id' => $location->id, 'shift_id' => null, 'count' => 2]);

        $data = $this->showData($location, ['api']);

        $this->assertNull($data['needed_angel_types']);
        $this->assertNull($data['shifts_count']);
    }

    public function testShowNeededAngelTypes(): void
    {
        $location = Location::factory()->create();
        /** @var NeededAngelType $needed */
        $needed = NeededAngelType::factory()
            ->create(['location_id' => $location->id, 'shift_id' => null, 'count' => 2]);
        NeededAngelType::factory()->create(['location_id' => $location->id, 'shift_id' => null, 'count' => 0]);

        $data = $this->showData($location, ['locations.view', 'admin_shifts']);

        $this->assertEquals([[
            'angel_type' => ['id' => $needed->angelType->id, 'name' => $needed->angelType->name],
            'count' => 2,
        ]], $data['needed_angel_types']);
        $this->assertNull($data['shifts_count']);
    }

    public function testShowShiftsCount(): void
    {
        $location = Location::factory()->create();
        Shift::factory(2)->create(['location_id' => $location->id]);

        $data = $this->showData($location, ['locations.view', 'locations.edit']);

        $this->assertNull($data['needed_angel_types']);
        $this->assertEquals(2, $data['shifts_count']);
    }

    public function testIndexWithDetailPrivileges(): void
    {
        $location = Location::factory()->create();
        Shift::factory()->create(['location_id' => $location->id]);

        $controller = new LocationsController(new Response());
        $controller->setAuth($this->auth(['admin_shifts', 'locations.edit']));

        $response = $controller->index();
        $this->validateApiResponse('/locations', 'get', $response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals([], $data['data'][0]['needed_angel_types']);
        $this->assertEquals(1, $data['data'][0]['shifts_count']);
    }

    public function testHasPermission(): void
    {
        $controller = new LocationsController(new Response());

        $controller->setAuth($this->auth(['locations.view']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'show'));

        $controller->setAuth($this->auth(['api']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));

        $controller->setAuth($this->auth(['news']));
        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
        $this->assertFalse($controller->hasPermission(new Request(), 'show'));
    }

    public function testHasPermissionWithoutAuthenticatedUser(): void
    {
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, null);

        $controller = new LocationsController(new Response());
        $controller->setAuth($auth);

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    public function testHasPermissionWithoutAuthSet(): void
    {
        $controller = new LocationsController(new Response());

        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
    }

    /**
     * @param string[] $privileges
     */
    protected function showData(Location $location, array $privileges): array
    {
        $request = new Request();
        $request = $request->withAttribute('location_id', (string) $location->id);

        $controller = new LocationsController(new Response());
        $controller->setAuth($this->auth($privileges));

        $response = $controller->show($request);
        $this->validateApiResponse('/locations/{id}', 'get', $response);

        return json_decode($response->getContent(), true)['data'];
    }

    /**
     * @param string[] $privileges
     */
    protected function auth(array $privileges): Authenticator
    {
        $auth = $this->createMock(Authenticator::class);
        $auth->method('user')->willReturn(User::factory()->create());
        $auth->method('can')->willReturnCallback(
            fn(array|string $abilities): bool => empty(array_diff((array) $abilities, $privileges))
        );

        return $auth;
    }
}
