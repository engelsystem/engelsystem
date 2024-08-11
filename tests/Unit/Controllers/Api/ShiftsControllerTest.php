<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\ShiftsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShiftsControllerTest extends ApiBaseControllerTest
{
    protected Location $location;
    protected Schedule $schedule;
    protected Shift $shiftA;
    protected Shift $shiftB;

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByLocation
     * @covers \Engelsystem\Controllers\Api\ShiftsController::shiftEntriesResponse
     * @covers \Engelsystem\Controllers\Api\Resources\ShiftResource::toArray
     * @covers \Engelsystem\Controllers\Api\Resources\ShiftTypeResource::toArray
     * @covers \Engelsystem\Controllers\Api\Resources\ShiftWithEntriesResource::toArray
     * @covers \Engelsystem\Controllers\Api\ShiftsController::getNeededAngelTypes
     */
    public function testEntriesByLocation(): void
    {
        $request = new Request();
        $request = $request->withAttribute('location_id', $this->location->id);

        $controller = new ShiftsController(new Response());

        $response = $controller->entriesByLocation($request);
        $this->validateApiResponse('/locations/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);

        // First shift
        $shiftAData = $data['data'][0];
        $this->assertEquals($this->shiftA->title, $shiftAData['name'], 'Title is equal');
        $this->assertEquals($this->location->id, $shiftAData['location']['id'], 'Same location');
        $this->assertEquals($this->shiftA->shiftType->id, $shiftAData['shift_type']['id'], 'Shift type equals');
        $this->assertCount(4, $shiftAData['needed_angel_types']);
        // Has users
        $entriesA = collect($shiftAData['needed_angel_types'])->sortBy('angeltype.id');
        $entry = $entriesA[0];
        $this->assertCount(2, $entry['entries']);
        $this->assertEquals(2, $entry['needs']);
        $user = $entry['entries'][0]['user'];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayNotHasKey('email', $user);
        $this->assertCount(0, $entriesA[1]['entries']);
        $this->assertCount(1, $entriesA[2]['entries']);
        $this->assertCount(1, $entriesA[3]['entries']);

        // Second (empty) shift
        $shiftBData = $data['data'][1];
        $this->assertEquals($this->shiftB->title, $shiftBData['name'], 'Title is equal');
        $this->assertEquals($this->location->id, $shiftBData['location']['id'], 'Same location');
        $this->assertEquals($this->shiftB->shiftType->id, $shiftBData['shift_type']['id'], 'Shift type equals');
        $this->assertCount(3, $shiftBData['needed_angel_types']);
        // No users
        $entriesB = collect($shiftBData['needed_angel_types'])->sortBy('angeltype.id');
        $this->assertCount(0, $entriesB[0]['entries']);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByAngeltype
     * @covers \Engelsystem\Controllers\Api\ShiftsController::getNeededAngelTypes
     */
    public function testEntriesByAngelType(): void
    {
        $this->schedule->needed_from_shift_type = true;
        $this->schedule->save();

        /** @var ShiftEntry $firstEntry */
        $firstEntry = $this->shiftB->shiftEntries->first();

        $request = new Request();
        $request = $request->withAttribute('angeltype_id', $firstEntry->angelType->id);

        $controller = new ShiftsController(new Response());

        $response = $controller->entriesByAngeltype($request);
        $this->validateApiResponse('/angeltypes/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(5, $data['data']);

        $shift = $data['data'][0];
        $this->assertTrue(count($shift['needed_angel_types']) >= 1);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByShiftType
     */
    public function testEntriesByShiftType(): void
    {
        /** @var ShiftEntry $firstEntry */
        $firstEntry = $this->shiftA->shiftEntries->first();

        $request = new Request();
        $request = $request->withAttribute('shifttype_id', $firstEntry->shift->shift_type_id);

        $controller = new ShiftsController(new Response());

        $response = $controller->entriesByShiftType($request);
        $this->validateApiResponse('/shifttypes/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $shift = $data['data'][0];
        $this->assertTrue(count($shift['needed_angel_types']) >= 1);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByUser
     */
    public function testEntriesByUser(): void
    {
        /** @var ShiftEntry $firstEntry */
        $firstEntry = $this->shiftA->shiftEntries->first();

        $request = new Request();
        $request = $request->withAttribute('user_id', $firstEntry->user->id);

        $controller = new ShiftsController(new Response());

        $response = $controller->entriesByUser($request);
        $this->validateApiResponse('/users/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $shift = $data['data'][0];
        $this->assertTrue(count($shift['needed_angel_types']) >= 1);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByUser
     */
    public function testEntriesByUserSelf(): void
    {
        $user = User::query()->first();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user);

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new ShiftsController(new Response());
        $controller->setAuth($auth);

        $response = $controller->entriesByUser($request);
        $this->validateApiResponse('/users/{id}/shifts', 'get', $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\ShiftsController::entriesByUser
     */
    public function testEntriesByUserNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('user_id', 42);

        $controller = new ShiftsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->entriesByUser($request);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->location = Location::factory()->create();
        $this->schedule = Schedule::factory()->create();

        // Shifts
        $this->shiftA = Shift::factory(1)
            ->create(['location_id' => $this->location->id, 'start' => Carbon::now()->subHour()])
            ->first();
        $this->shiftB = Shift::factory(1)
            ->create(['location_id' => $this->location->id, 'start' => Carbon::now()->addHour()])
            ->first();
        (new ScheduleShift(['shift_id' => $this->shiftB->id, 'schedule_id' => $this->schedule->id, 'guid' => 'a']))
            ->save();

        // "Empty" entry to be skipped
        NeededAngelType::factory(1)->create(['location_id' => null, 'shift_id' => $this->shiftA->id, 'count' => 0]);

        // Needed entry by shift
        /** @var NeededAngelType $byShift */
        $byShift = NeededAngelType::factory(2)
            ->create(['location_id' => null, 'shift_type_id' => null, 'shift_id' => $this->shiftA->id, 'count' => 2])
            ->first();

        // Needed entry by location
        /** @var NeededAngelType $byLocation */
        $byLocation = NeededAngelType::factory(1)
            ->create(['location_id' => $this->location->id, 'shift_type_id' => null, 'shift_id' => null, 'count' => 3])
            ->first();

        // Needed entry by shift type
        $shiftType = $this->shiftB->shiftType;
        /** @var NeededAngelType $byShiftType */
        $byShiftType = NeededAngelType::factory(2)
            ->create(['location_id' => null, 'shift_type_id' => $shiftType->id, 'count' => 3])
            ->first();
        ShiftEntry::factory(5)->create([
            'shift_id' => $this->shiftB->id,
            'angel_type_id' => $byShiftType->angel_type_id,
        ]);

        // Added by both
        NeededAngelType::factory(1)
            ->create([
                'location_id' => $this->location->id,
                'shift_type_id' => null,
                'shift_id' => null,
                'angel_type_id' => $byShift->angel_type_id,
                'count' => 3,
            ])
            ->first();

        // By shift
        ShiftEntry::factory(2)->create([
            'shift_id' => $this->shiftA->id,
            'angel_type_id' => $byShift->angel_type_id,
        ]);

        // By location
        ShiftEntry::factory(1)->create([
            'shift_id' => $this->shiftA->id,
            'angel_type_id' => $byLocation->angel_type_id,
        ]);

        // Additional (not required by shift nor location)
        ShiftEntry::factory(1)->create(['shift_id' => $this->shiftA->id]);

        $this->schedule->shiftType()->associate($shiftType);

        foreach (User::all() as $user) {
            // Generate user data
            /** @var User $user */
            PersonalData::factory()->create(['user_id' => $user->id]);
            Contact::factory()->create(['user_id' => $user->id]);
        }
    }
}
