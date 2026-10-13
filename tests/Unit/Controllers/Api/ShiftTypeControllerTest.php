<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\ShiftTypeResource;
use Engelsystem\Controllers\Api\ShiftTypeController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ShiftTypeController::class, 'index')]
#[CoversMethod(ShiftTypeController::class, 'show')]
#[CoversMethod(ShiftTypeController::class, 'withDetails')]
#[CoversMethod(ShiftTypeController::class, 'shiftTypeData')]
#[CoversMethod(ShiftTypeController::class, 'hasPermission')]
#[CoversMethod(ShiftTypeController::class, 'webPermission')]
#[CoversMethod(ShiftTypeResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class ShiftTypeControllerTest extends ApiBaseControllerTestCase
{
    public function testIndex(): void
    {
        $items = ShiftType::factory(3)->create();

        $controller = new ShiftTypeController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/shifttypes', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, $data['data']);
        $this->assertCount(1, collect($data['data'])->filter(function ($item) use ($items) {
            return $item['name'] == $items->first()->getAttribute('name');
        }));
    }

    public function testShow(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['signup_advance_hours' => 12.5]);
        NeededAngelType::factory()
            ->create(['location_id' => null, 'shift_id' => null, 'shift_type_id' => $shiftType->id, 'count' => 2]);

        $data = $this->showData($shiftType, ['user_shifts']);

        $this->assertEquals($shiftType->id, $data['id']);
        $this->assertEquals($shiftType->name, $data['name']);
        $this->assertEquals($shiftType->description, $data['description']);
        $this->assertEquals(12.5, $data['signup_advance_hours']);
        $this->assertNull($data['needed_angel_types']);
    }

    public function testShowNeededAngelTypes(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create(['signup_advance_hours' => null]);
        /** @var NeededAngelType $needed */
        $needed = NeededAngelType::factory()
            ->create(['location_id' => null, 'shift_id' => null, 'shift_type_id' => $shiftType->id, 'count' => 3]);
        NeededAngelType::factory()
            ->create(['location_id' => null, 'shift_id' => null, 'shift_type_id' => $shiftType->id, 'count' => 0]);

        $data = $this->showData($shiftType, ['api', 'shifttypes.view']);

        $this->assertNull($data['signup_advance_hours']);
        $this->assertEquals([[
            'angel_type' => ['id' => $needed->angelType->id, 'name' => $needed->angelType->name],
            'count' => 3,
        ]], $data['needed_angel_types']);
    }

    public function testShowNeededAngelTypesNotWithApiPrivilegeOnly(): void
    {
        /** @var ShiftType $shiftType */
        $shiftType = ShiftType::factory()->create();

        $data = $this->showData($shiftType, ['api']);

        $this->assertNull($data['needed_angel_types']);
    }

    public function testShowNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('shifttype_id', '999999');

        $controller = new ShiftTypeController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->show($request);
    }

    public function testHasPermission(): void
    {
        $controller = new ShiftTypeController(new Response());

        $controller->setAuth($this->auth(['user_shifts']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'show'));

        $controller->setAuth($this->auth(['api']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));

        $controller->setAuth($this->auth(['news']));
        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
        $this->assertFalse($controller->hasPermission(new Request(), 'show'));
    }

    /**
     * @param string[] $privileges
     */
    protected function showData(ShiftType $shiftType, array $privileges): array
    {
        $request = new Request();
        $request = $request->withAttribute('shifttype_id', (string) $shiftType->id);

        $controller = new ShiftTypeController(new Response());
        $controller->setAuth($this->auth($privileges));

        $response = $controller->show($request);
        $this->validateApiResponse('/shifttypes/{id}', 'get', $response);

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
