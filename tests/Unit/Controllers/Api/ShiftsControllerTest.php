<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\ShiftResource;
use Engelsystem\Controllers\Api\Resources\ShiftTypeResource;
use Engelsystem\Controllers\Api\Resources\ShiftWithEntriesResource;
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
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Tag;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ShiftsController::class, 'entriesByLocation')]
#[CoversMethod(ShiftsController::class, 'shiftEntriesResponse')]
#[CoversMethod(ShiftResource::class, 'toArray')]
#[CoversMethod(ShiftTypeResource::class, 'toArray')]
#[CoversMethod(ShiftWithEntriesResource::class, 'toArray')]
#[CoversMethod(ShiftsController::class, 'getNeededAngelTypes')]
#[CoversMethod(ShiftsController::class, 'entriesByAngeltype')]
#[CoversMethod(ShiftsController::class, 'entriesByShiftType')]
#[CoversMethod(ShiftsController::class, 'entriesByUser')]
#[CoversMethod(ShiftsController::class, 'freeloadDetailsVisibility')]
#[CoversMethod(ShiftsController::class, 'supportedAngelTypes')]
#[CoversMethod(ShiftsController::class, 'editorData')]
#[CoversMethod(ShiftsController::class, 'entryDetails')]
#[CoversMethod(ShiftsController::class, 'webPermission')]
#[CoversMethod(ShiftsController::class, 'hasPermission')]
#[AllowMockObjectsWithoutExpectations]
class ShiftsControllerTest extends ApiBaseControllerTestCase
{
    protected Location $location;
    protected Schedule $schedule1;
    protected Schedule $schedule2;
    protected Shift $shiftA;
    protected Shift $shiftB;
    protected Shift $shiftC;
    protected Shift $shiftD;
    protected ShiftType $shiftType;

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
        $this->assertCount(1, $data['data']);

        // First shift
        $shiftCData = $data['data'][0];
        $this->assertEquals($this->shiftC->title, $shiftCData['name'], 'Title is equal');
        $this->assertEquals($this->location->id, $shiftCData['location']['id'], 'Same location');
        $this->assertEquals($this->shiftC->shiftType->id, $shiftCData['shift_type']['id'], 'Shift type equals');
        $this->assertCount(2, $shiftCData['needed_angel_types']);
        // Has no users
        $entriesC = collect($shiftCData['needed_angel_types'])->sortBy('angeltype.id');
        $entry = $entriesC[0];
        $this->assertCount(0, $entry['entries']);
        $this->assertEquals(3, $entry['needs']);
        // Has users
        $entry = $entriesC[1];
        $this->assertCount(3, $entry['entries']);
        $this->assertEquals(0, $entry['needs']);
        $shiftEntry = $entry['entries'][0];
        $this->assertArrayHasKey('id', $shiftEntry);
        $this->assertIsInt($shiftEntry['id']);
        $user = $shiftEntry['user'];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayNotHasKey('email', $user);
        $this->assertCount(0, $entriesC[0]['entries']);
        $this->assertCount(3, $entriesC[1]['entries']);
    }

    public function testEntriesByLocationNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('location_id', 42);

        $controller = new ShiftsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->entriesByLocation($request);
    }

    public function testEntriesByAngelType(): void
    {
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
        $this->assertCount(3, $data['data']);

        $shift = $data['data'][0];
        $this->assertNotEmpty($shift['needed_angel_types']);
    }

    public function testEntriesByAngelTypeNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('angeltype_id', 42);

        $controller = new ShiftsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->entriesByAngeltype($request);
    }

    public function testEntriesByShiftType(): void
    {
        /** @var ShiftEntry $firstEntry */
        $firstEntry = $this->shiftA->shiftEntries->first();

        $request = new Request();
        $request = $request->withAttribute('shifttype_id', $firstEntry->shift->shift_type_id);

        $controller = new ShiftsController(new Response());
        $controller->setAuth($this->auth(User::factory()->create(), ['api']));

        $response = $controller->entriesByShiftType($request);
        $this->validateApiResponse('/shifttypes/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $shift = $data['data'][0];
        $this->assertNotEmpty($shift['needed_angel_types']);

        $freeloaded = $this->freeloadedEntry($data);
        $this->assertTrue($freeloaded['freeloaded']);
        $this->assertNotEmpty($freeloaded['freeloaded_by']);
        $this->assertEquals(User::first()->id, $freeloaded['freeloaded_by']['id']);
    }

    public function testShiftFields(): void
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->shiftA->shiftType->update(['signup_advance_hours' => 2]);
        $this->shiftA->update([
            'url' => 'https://events.example.com/talk/42',
            'start' => Carbon::parse('2026-12-27 03:00'),
            'end' => Carbon::parse('2026-12-27 05:00'),
            'created_by' => $editor->id,
            'updated_by' => $editor->id,
        ]);
        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $this->shiftA->tags()->attach($tag);

        $shift = $this->shiftAData(['api']);

        $this->assertEquals('https://events.example.com/talk/42', $shift['talk_url']);
        $this->assertEquals([['id' => $tag->id, 'name' => $tag->name]], $shift['tags']);
        $this->assertTrue($shift['night_shift']);
        $this->assertEquals($this->shiftA->shiftType->description, $shift['shift_type']['description']);
        $this->assertEquals(Carbon::parse('2026-12-27 01:00'), Carbon::parse($shift['signup_available_from']));
        $this->assertEquals(Carbon::parse('2026-12-27 00:00'), Carbon::parse($shift['sign_out_until']));
        $this->assertNull($shift['created_by']);
        $this->assertNull($shift['updated_by']);

        $shift = $this->shiftAData(['admin_shifts']);
        $this->assertEquals($editor->id, $shift['created_by']['id']);
        $this->assertEquals($editor->id, $shift['updated_by']['id']);
    }

    public function testShiftFieldsDefaults(): void
    {
        $this->shiftA->shiftType->update(['signup_advance_hours' => null]);
        $this->shiftA->update(['url' => '']);

        $shift = $this->shiftAData(['user_shifts']);

        $this->assertNull($shift['talk_url']);
        $this->assertEquals([], $shift['tags']);
        $this->assertNull($shift['signup_available_from']);
    }

    public function testEntriesByUserOfOtherUserFiltered(): void
    {
        /** @var ShiftEntry $entry */
        $entry = $this->shiftA->shiftEntries()->whereNotNull('freeloaded_by')->first();
        $request = (new Request())->withAttribute('user_id', $entry->user_id);

        $controller = new ShiftsController(new Response());

        // Regular user without the api privilege
        $controller->setAuth($this->auth(User::factory()->create(), ['user_shifts']));
        $data = json_decode($controller->entriesByUser($request)->getContent(), true);
        $this->assertCount(0, $data['data']);

        // Supporter of the entry's angel type
        /** @var User $supporter */
        $supporter = User::factory()->create();
        $supporter->userAngelTypes()->attach($entry->angel_type_id, ['supporter' => true]);
        $controller->setAuth($this->auth($supporter, []));
        $response = $controller->entriesByUser($request);
        $this->validateApiResponse('/users/{id}/shifts', 'get', $response);
        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data['data']);

        // Shift admin
        $controller->setAuth($this->auth(User::factory()->create(), ['user_shifts_admin']));
        $data = json_decode($controller->entriesByUser($request)->getContent(), true);
        $this->assertCount(1, $data['data']);

        // The user themself
        $controller->setAuth($this->auth($entry->user, []));
        $data = json_decode($controller->entriesByUser($request)->getContent(), true);
        $this->assertCount(1, $data['data']);
    }

    public function testHasPermission(): void
    {
        $controller = new ShiftsController(new Response());

        $controller->setAuth($this->auth(User::factory()->create(), ['user_shifts']));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByShiftType'));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByUser'));
        $this->assertFalse($controller->hasPermission(new Request(), 'entriesByLocation'));
        $this->assertFalse($controller->hasPermission(new Request(), 'entriesByAngeltype'));

        $controller->setAuth($this->auth(User::factory()->create(), ['locations.view', 'angeltypes.view']));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByLocation'));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByAngeltype'));
        $this->assertFalse($controller->hasPermission(new Request(), 'entriesByShiftType'));

        $controller->setAuth($this->auth(User::factory()->create(), ['api']));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByLocation'));
    }

    /**
     * @param string[] $privileges
     */
    protected function shiftAData(array $privileges, ?User $user = null): array
    {
        $request = new Request();
        $request = $request->withAttribute('shifttype_id', $this->shiftA->shift_type_id);

        $controller = new ShiftsController(new Response());
        $controller->setAuth($this->auth($user ?? User::factory()->create(), $privileges));

        $response = $controller->entriesByShiftType($request);
        $this->validateApiResponse('/shifttypes/{id}/shifts', 'get', $response);

        return collect(json_decode($response->getContent(), true)['data'])
            ->firstWhere('id', $this->shiftA->id);
    }

    public function testEntryDetails(): void
    {
        $this->config->set('last_unsubscribe', 3);
        $this->shiftA->update(['start' => Carbon::now()->addDay(), 'end' => Carbon::now()->addDay()->addHour()]);
        /** @var ShiftEntry $freeloaded */
        $freeloaded = $this->shiftA->shiftEntries()->whereNotNull('freeloaded_by')->first();
        $freeloaded->update(['freeloaded_comment' => 'Did not show up', 'user_comment' => 'Own note']);
        /** @var ShiftEntry $other */
        $other = $this->shiftA->shiftEntries()->whereNull('freeloaded_by')->first();

        // The user of the entry: own comment, can sign out before the deadline
        $entries = $this->entriesById($this->shiftAData(['user_shifts'], $freeloaded->user));
        $this->assertEquals('Own note', $entries[$freeloaded->id]['user_comment']);
        $this->assertNull($entries[$freeloaded->id]['freeloaded_comment']);
        $this->assertTrue($entries[$freeloaded->id]['can_sign_out']);
        $this->assertNull($entries[$other->id]['user_comment']);
        $this->assertFalse($entries[$other->id]['can_sign_out']);

        // "api" doesn't grant the new details
        $entries = $this->entriesById($this->shiftAData(['api']));
        $this->assertNull($entries[$freeloaded->id]['freeloaded_comment']);
        $this->assertNull($entries[$freeloaded->id]['user_comment']);
        $this->assertFalse($entries[$freeloaded->id]['can_sign_out']);

        // Shift admins see the freeload comment and can sign out everyone
        $entries = $this->entriesById($this->shiftAData(['user_shifts_admin']));
        $this->assertEquals('Did not show up', $entries[$freeloaded->id]['freeloaded_comment']);
        $this->assertNull($entries[$other->id]['freeloaded_comment']);
        $this->assertTrue($entries[$other->id]['can_sign_out']);

        // Supporters only for their angel type
        /** @var User $supporter */
        $supporter = User::factory()->create();
        $supporter->userAngelTypes()->attach($freeloaded->angel_type_id, ['supporter' => true]);
        $entries = $this->entriesById($this->shiftAData([], $supporter));
        $this->assertEquals('Did not show up', $entries[$freeloaded->id]['freeloaded_comment']);
        $this->assertTrue($entries[$freeloaded->id]['can_sign_out']);
        if ($other->angel_type_id != $freeloaded->angel_type_id) {
            $this->assertFalse($entries[$other->id]['can_sign_out']);
        }

        // After the deadline the user can't sign out anymore
        $this->shiftA->update(['start' => Carbon::now()->addHour(), 'end' => Carbon::now()->addHours(2)]);
        $entries = $this->entriesById($this->shiftAData(['user_shifts'], $freeloaded->user));
        $this->assertFalse($entries[$freeloaded->id]['can_sign_out']);
    }

    protected function entriesById(array $shift): array
    {
        $entries = [];
        foreach ($shift['needed_angel_types'] as $needed) {
            foreach ($needed['entries'] as $entry) {
                $entries[$entry['id']] = $entry;
            }
        }

        return $entries;
    }

    public function testFreeloadedByHiddenWithoutApiPrivilege(): void
    {
        $data = $this->freeloadResponse(User::factory()->create(), ['user_shifts']);

        $freeloaded = $this->freeloadedEntry($data);
        $this->assertTrue($freeloaded['freeloaded']);
        $this->assertNull($freeloaded['freeloaded_by']);
    }

    public function testFreeloadedByVisibleToShiftAdmin(): void
    {
        $data = $this->freeloadResponse(User::factory()->create(), ['user_shifts_admin']);

        $freeloaded = $this->freeloadedEntry($data);
        $this->assertEquals(User::first()->id, $freeloaded['freeloaded_by']['id']);
    }

    public function testFreeloadedByVisibleToAngelTypeSupporter(): void
    {
        /** @var ShiftEntry $entry */
        $entry = $this->shiftA->shiftEntries()->whereNotNull('freeloaded_by')->first();

        /** @var User $supporter */
        $supporter = User::factory()->create();
        $supporter->userAngelTypes()->attach($entry->angel_type_id, ['supporter' => true]);

        /** @var User $member */
        $member = User::factory()->create();
        $member->userAngelTypes()->attach($entry->angel_type_id, ['supporter' => false]);

        $freeloaded = $this->freeloadedEntry($this->freeloadResponse($supporter, []));
        $this->assertEquals(User::first()->id, $freeloaded['freeloaded_by']['id']);

        $freeloaded = $this->freeloadedEntry($this->freeloadResponse($member, []));
        $this->assertNull($freeloaded['freeloaded_by']);
    }

    /**
     * @param string[] $privileges
     */
    protected function freeloadResponse(User $user, array $privileges): array
    {
        $request = new Request();
        $request = $request->withAttribute('shifttype_id', $this->shiftA->shift_type_id);

        $controller = new ShiftsController(new Response());
        $controller->setAuth($this->auth($user, $privileges));

        $response = $controller->entriesByShiftType($request);
        $this->validateApiResponse('/shifttypes/{id}/shifts', 'get', $response);

        return json_decode($response->getContent(), true);
    }

    protected function freeloadedEntry(array $data): array
    {
        foreach ($data['data'] as $shift) {
            foreach ($shift['needed_angel_types'] as $needed) {
                foreach ($needed['entries'] as $entry) {
                    if ($entry['freeloaded']) {
                        return $entry;
                    }
                }
            }
        }

        $this->fail('No freeloaded entry found');
    }

    /**
     * @param string[] $privileges
     */
    protected function auth(User $user, array $privileges): Authenticator
    {
        $auth = $this->createMock(Authenticator::class);
        $auth->method('user')->willReturn($user);
        $auth->method('can')->willReturnCallback(
            fn(array|string $abilities): bool => empty(array_diff((array) $abilities, $privileges))
        );

        return $auth;
    }

    public function testEntriesByShiftTypeNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('shifttype_id', 42);

        $controller = new ShiftsController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->entriesByShiftType($request);
    }

    public function testEntriesByUser(): void
    {
        /** @var ShiftEntry $firstEntry */
        $firstEntry = $this->shiftA->shiftEntries->first();

        $request = new Request();
        $request = $request->withAttribute('user_id', $firstEntry->user->id);

        $controller = new ShiftsController(new Response());
        $controller->setAuth($this->auth(User::factory()->create(), ['api']));

        $response = $controller->entriesByUser($request);
        $this->validateApiResponse('/users/{id}/shifts', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $shift = $data['data'][0];
        $this->assertNotEmpty($shift['needed_angel_types']);
    }

    public function testEntriesByUserSelf(): void
    {
        $user = User::query()->first();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new ShiftsController(new Response());
        $controller->setAuth($auth);

        $response = $controller->entriesByUser($request);
        $this->validateApiResponse('/users/{id}/shifts', 'get', $response);
    }

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

        $this->config->set('night_shifts', ['enabled' => true, 'start' => 2, 'end' => 8, 'multiplier' => 2]);
        $this->config->set('last_unsubscribe', 3);
        $this->config->set('signup_advance_hours', 0);

        /*
         * Shift A: Direct shift entries (2 needed)
         * Shift B: Needed by schedule 1 via shift type
         * Shift C: Needed by schedule 2 via location
         */

        $this->location = Location::factory()->create();
        $this->shiftType = ShiftType::factory()->create();
        $this->schedule1 = Schedule::factory()->create([
            'needed_from_shift_type' => true,
            'shift_type' => $this->shiftType->id,
        ]);
        $this->schedule2 = Schedule::factory()->create(['needed_from_shift_type' => false]);

        // Shifts
        $this->shiftA = Shift::factory()->create(['start' => Carbon::now()->subHour()]);
        $this->shiftB = Shift::factory()->create(['start' => Carbon::now()->addHour()]);
        $this->shiftC = Shift::factory()->create([
            'location_id' => $this->location->id,
            'start' => Carbon::now()->addHour(),
        ]);
        $this->shiftD = Shift::factory()->create([
            'start' => Carbon::now()->addHour(),
            'location_id' => $this->location->id,
        ]);

        (new ScheduleShift([
            'shift_id' => $this->shiftB->id,
            'schedule_id' => $this->schedule1->id,
            'guid' => Str::uuid(),
        ]))
            ->save();
        (new ScheduleShift([
            'shift_id' => $this->shiftC->id,
            'schedule_id' => $this->schedule2->id,
            'guid' => Str::uuid(),
        ]))
            ->save();

        // "Empty" entry to be skipped
        NeededAngelType::factory(1)
            ->create(['location_id' => null, 'shift_id' => $this->shiftA->id, 'shift_type_id' => null, 'count' => 0]);

        // Needed entry by shift
        /** @var NeededAngelType $byShift */
        $byShift = NeededAngelType::factory(2)
            ->create(['location_id' => null, 'shift_id' => $this->shiftA->id, 'shift_type_id' => null, 'count' => 2])
            ->first();

        // Needed entry by location via schedule
        /** @var NeededAngelType $byLocation */
        $byLocation = NeededAngelType::factory()
            ->create(['location_id' => $this->location->id, 'shift_id' => null, 'shift_type_id' => null, 'count' => 3])
            ->first();

        // Needed entry by shift type via schedule
        /** @var NeededAngelType $byShiftType */
        $byShiftType = NeededAngelType::factory()
            ->create(['location_id' => null, 'shift_id' => null, 'shift_type_id' => $this->shiftType->id, 'count' => 5])
            ->first();

        // By shift
        ShiftEntry::factory(1)->create([
            'shift_id' => $this->shiftA->id,
            'angel_type_id' => $byShift->angel_type_id,
            'freeloaded_by' => User::first()->id,
        ]);

        // By location via schedule
        ShiftEntry::factory(2)->create([
            'shift_id' => $this->shiftB->id,
            'angel_type_id' => $byLocation->angel_type_id,
            'freeloaded_by' => null,
        ]);

        // By shift type via schedule
        ShiftEntry::factory(3)->create([
            'shift_id' => $this->shiftC->id,
            'angel_type_id' => $byShiftType->angel_type_id,
            'freeloaded_by' => null,
        ]);

        // Additional (not required by shift nor location)
        ShiftEntry::factory(5)->create(['shift_id' => $this->shiftA->id, 'freeloaded_by' => null]);

        foreach (User::all() as $user) {
            // Generate user data
            /** @var User $user */
            PersonalData::factory()->create(['user_id' => $user->id]);
            Contact::factory()->create(['user_id' => $user->id]);
        }
    }
}
