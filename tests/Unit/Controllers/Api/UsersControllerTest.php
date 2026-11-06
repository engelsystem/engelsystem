<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Config\GoodieType;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeReferenceResource;
use Engelsystem\Controllers\Api\Resources\UserDetailResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Controllers\Api\Resources\WorklogResource;
use Engelsystem\Controllers\Api\UsersController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(UsersController::class, 'index')]
#[CoversMethod(UsersController::class, 'user')]
#[CoversMethod(UserDetailResource::class, 'toArray')]
#[CoversMethod(UserResource::class, 'toArray')]
#[CoversMethod(UsersController::class, 'entriesByAngeltype')]
#[CoversMethod(UserAngelTypeReferenceResource::class, 'toArray')]
#[CoversMethod(UsersController::class, 'worklogs')]
#[CoversMethod(UsersController::class, 'hasPermission')]
#[CoversMethod(UsersController::class, 'webPermission')]
#[CoversMethod(UsersController::class, 'publicUserData')]
#[CoversMethod(UsersController::class, 'profileData')]
#[CoversMethod(UsersController::class, 'adminStateData')]
#[CoversMethod(UsersController::class, 'profileShiftsVisible')]
#[CoversMethod(UsersController::class, 'memberData')]
#[CoversMethod(WorklogResource::class, 'toArray')]
#[AllowMockObjectsWithoutExpectations]
class UsersControllerTest extends ApiBaseControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->config->set([
            'goodie_type' => GoodieType::None->value,
            'enable_voucher' => false,
            'enable_full_name' => false,
            'enable_pronoun' => true,
            'enable_dect' => true,
            'enable_mobile_show' => true,
            'enable_force_active' => true,
            'enable_force_food' => false,
            'driving_license_enabled' => true,
            'ifsg_enabled' => true,
            'ifsg_light_enabled' => false,
            'max_freeloadable_shifts' => 2,
            'night_shifts' => ['enabled' => false, 'start' => 2, 'end' => 8, 'multiplier' => 2],
            'voucher_settings' => [
                'initial_vouchers' => 0,
                'shifts_per_voucher' => 0,
                'hours_per_voucher' => 2,
                'voucher_start' => null,
            ],
        ]);
    }

    public function testIndex(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $controller = new UsersController(new Response());

        $response = $controller->index();
        $this->validateApiResponse('/users', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);

        $firstUser = $data['data'][0];
        $this->assertArrayHasKey('id', $firstUser);
        $this->assertEquals($user->id, $firstUser['id']);
        $this->assertArrayHasKey('name', $firstUser);
        $this->assertEquals($user->name, $firstUser['name']);
    }

    public function testUser(): void
    {
        $user = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory())
            ->has(OAuth::factory())
            ->create();

        /** @var Group $group */
        $group = Group::factory()->create(['name' => 'Some group']);
        $group->privileges()->attach(Privilege::factory()->create(['name' => 'user_shifts']));
        $group->privileges()->attach(Privilege::factory()->create(['name' => 'news']));
        $user->groups()->attach($group);

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new UsersController(new Response());
        $controller->setAuth($auth);

        $response = $controller->user($request);
        $this->validateApiResponse('/users/{id}', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals($user->id, $data['data']['id']);
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertEquals($user->name, $data['data']['name']);
        $this->assertArrayHasKey('email', $data['data']);
        $this->assertArrayHasKey('dates', $data['data']);
        $this->assertArrayHasKey('contact', $data['data']);
        $this->assertArrayHasKey('oauth', $data['data']);
        $this->assertEquals([['id' => $group->id, 'name' => 'Some group']], $data['data']['groups']);
        $this->assertEquals(['news', 'user_shifts'], $data['data']['privileges']);
    }

    public function testUserById(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory())
            ->create();

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', $otherUser->id);

        $controller = new UsersController(new Response());
        $controller->setAuth($auth);

        $response = $controller->user($request);
        $this->validateApiResponse('/users/{id}', 'get', $response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertEquals($otherUser->id, $data['data']['id']);
        $this->assertArrayNotHasKey('dates', $data['data']);
        $this->assertArrayNotHasKey('oauth', $data['data']);
    }

    public function testUserNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('user_id', 42);

        $controller = new UsersController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->user($request);
    }

    public function testEntriesByAngeltype(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $controller = new UsersController(new Response());
        $controller->setAuth($this->auth(['api']));
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $user->userAngelTypes()->attach($angelType);
        $request = new Request([], [], ['angeltype_id' => $angelType->id]);

        $response = $controller->entriesByAngeltype($request);
        $this->validateApiResponse('/angeltypes/{id}/users', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);

        $firstEntry = $data['data'][0];
        $this->assertArrayHasKey('user', $firstEntry);
        $this->assertArrayHasKey('confirmed', $firstEntry);
        $this->assertArrayHasKey('supporter', $firstEntry);

        $firstUser = $firstEntry['user'];
        $this->assertArrayHasKey('id', $firstUser);
        $this->assertEquals($user->id, $firstUser['id']);
    }

    public function testWorklogs(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $controller = new UsersController(new Response());
        $controller->setAuth($this->auth(['api']));
        /** @var Worklog $worklog */
        $worklog = Worklog::factory()->create(['user_id' => $user->id, 'hours' => 1.23]);
        $request = new Request([], [], ['user_id' => $user->id]);

        $response = $controller->worklogs($request);
        $this->validateApiResponse('/users/{id}/worklogs', 'get', $response);

        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);

        $firstEntry = $data['data'][0];
        $this->assertArrayHasKey('id', $firstEntry);
        $this->assertArrayHasKey('description', $firstEntry);
        $this->assertArrayHasKey('hours', $firstEntry);

        $this->assertEquals($worklog->hours, $firstEntry['hours']);
    }

    public function testWorklogsSelf(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        /** @var Worklog $worklog */
        $worklog = Worklog::factory()->create(['user_id' => $user->id, 'hours' => 1.23]);
        Worklog::factory()->create(['user_id' => $otherUser->id, 'hours' => 4.56]);

        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, $user, $this->atLeastOnce());

        $request = new Request();
        $request = $request->withAttribute('user_id', 'self');

        $controller = new UsersController(new Response());
        $controller->setAuth($auth);

        $response = $controller->worklogs($request);
        $this->validateApiResponse('/users/{id}/worklogs', 'get', $response);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertEquals($worklog->id, $data['data'][0]['id']);
        $this->assertEquals($worklog->hours, $data['data'][0]['hours']);
    }

    public function testWorklogsNotFound(): void
    {
        $request = new Request();
        $request = $request->withAttribute('user_id', 42);

        $controller = new UsersController(new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->worklogs($request);
    }

    public function testUserProfileWithoutApiPrivilege(): void
    {
        $otherUser = $this->profileUser();
        $otherUser->settings->update(['mobile_show' => false]);

        $data = $this->userData($otherUser, ['user_shifts']);

        $this->assertNull($data['first_name']);
        $this->assertNull($data['last_name']);
        $this->assertEquals($otherUser->personalData->pronoun, $data['pronoun']);
        $this->assertEquals($otherUser->contact->dect, $data['contact']['dect']);
        $this->assertNull($data['contact']['mobile']);
        $this->assertTrue($data['arrived']);
        $this->assertNull($data['vouchers']);
        $this->assertNull($data['goodie_score']);
        $this->assertNull($data['user_info']);
        $this->assertNull($data['admin_state']);
        $this->assertCount(1, $data['groups']);
        $this->assertNotNull($data['next_shift']);
    }

    public function testUserProfileWithApiPrivilege(): void
    {
        $otherUser = $this->profileUser();
        $otherUser->settings->update(['mobile_show' => false]);

        $data = $this->userData($otherUser, ['api']);

        $this->assertEquals($otherUser->personalData->first_name, $data['first_name']);
        $this->assertEquals($otherUser->contact->mobile, $data['contact']['mobile']);
        // New fields follow the website's rules also for "api" users
        $this->assertNull($data['admin_state']);
        $this->assertNull($data['user_info']);
    }

    public function testUserProfileAdminDetails(): void
    {
        $this->config->set('enable_voucher', true);
        $this->config->set('goodie_type', GoodieType::Goodie->value);
        $otherUser = $this->profileUser();
        $otherUser->state->update(['user_info' => 'Some info']);

        $data = $this->userData($otherUser, ['admin_user', 'user.info.view']);

        $this->assertEquals($otherUser->personalData->first_name, $data['first_name']);
        $this->assertEquals('Some info', $data['user_info']);
        $this->assertNotNull($data['vouchers']);
        $this->assertIsNumeric($data['goodie_score']);
        $this->assertTrue($data['admin_state']['active']);
        $this->assertNotNull($data['admin_state']['force_active']);
        $this->assertNull($data['admin_state']['force_food']);
        $this->assertFalse($data['admin_state']['freeloader']);
        $this->assertFalse($data['admin_state']['password_reset']);
    }

    public function testUserProfileSelf(): void
    {
        $this->config->set('enable_voucher', true);
        $this->config->set('goodie_type', GoodieType::Goodie->value);
        $user = $this->profileUser();

        $data = $this->userData($user, [], $user);

        $this->assertArrayHasKey('email', $data);
        $this->assertNotNull($data['vouchers']);
        $this->assertIsNumeric($data['goodie_score']);
        $this->assertNull($data['admin_state']);
    }

    public function testUserProfileNotArrived(): void
    {
        $otherUser = $this->profileUser();
        $otherUser->state->update(['arrival_date' => null]);

        $data = $this->userData($otherUser, ['user_shifts']);

        $this->assertFalse($data['arrived']);
        $this->assertNull($data['next_shift']);
    }

    public function testWorklogsOfOtherUsersFiltered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        Worklog::factory()->create(['user_id' => $user->id]);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $user->userAngelTypes()->attach($angelType);
        $request = new Request([], [], ['user_id' => $user->id]);
        $controller = new UsersController(new Response());

        $controller->setAuth($this->auth(['user_shifts']));
        $this->assertCount(0, json_decode($controller->worklogs($request)->getContent(), true)['data']);

        $controller->setAuth($this->auth(['admin_user']));
        $this->assertCount(1, json_decode($controller->worklogs($request)->getContent(), true)['data']);

        $controller->setAuth($this->auth(['user.goodie.edit']));
        $this->assertCount(1, json_decode($controller->worklogs($request)->getContent(), true)['data']);

        /** @var User $supporter */
        $supporter = User::factory()->create();
        $supporter->userAngelTypes()->attach($angelType, ['supporter' => true]);
        $controller->setAuth($this->auth([], $supporter));
        $response = $controller->worklogs($request);
        $this->validateApiResponse('/users/{id}/worklogs', 'get', $response);
        $this->assertCount(1, json_decode($response->getContent(), true)['data']);
    }

    public function testEntriesByAngeltypeMembers(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create([
            'restricted' => true,
            'requires_driver_license' => true,
            'requires_ifsg_certificate' => true,
        ]);
        $confirmed = $this->profileUser();
        $confirmed->userAngelTypes()->attach($angelType, ['confirm_user_id' => $confirmed->id]);
        License::factory()->create(['user_id' => $confirmed->id, 'drive_car' => true, 'drive_confirmed' => true]);
        $unconfirmed = $this->profileUser();
        $unconfirmed->userAngelTypes()->attach($angelType);
        $request = new Request([], [], ['angeltype_id' => $angelType->id]);
        $controller = new UsersController(new Response());

        // Regular member without the api privilege
        $controller->setAuth($this->auth(['angeltypes.view']));
        $response = $controller->entriesByAngeltype($request);
        $this->validateApiResponse('/angeltypes/{id}/users', 'get', $response);
        $data = json_decode($response->getContent(), true)['data'];
        $this->assertCount(1, $data);
        $this->assertEquals($confirmed->id, $data[0]['user']['id']);
        $this->assertEquals($confirmed->personalData->pronoun, $data[0]['pronoun']);
        $this->assertEquals($confirmed->contact->dect, $data[0]['dect']);
        $this->assertNull($data[0]['driver_license']);
        $this->assertNull($data[0]['ifsg']);
        $this->assertNull($data[0]['got_goodie']);

        // Supporter of the angel type: unconfirmed members and certificates
        /** @var User $supporter */
        $supporter = User::factory()->create();
        $supporter->userAngelTypes()->attach($angelType, ['supporter' => true, 'confirm_user_id' => $supporter->id]);
        $controller->setAuth($this->auth([], $supporter));
        $response = $controller->entriesByAngeltype($request);
        $this->validateApiResponse('/angeltypes/{id}/users', 'get', $response);
        $data = collect(json_decode($response->getContent(), true)['data'])->keyBy('user.id');
        $this->assertTrue($data->has($unconfirmed->id));
        $this->assertTrue($data[$confirmed->id]['driver_license']['car']);
        $this->assertTrue($data[$confirmed->id]['driver_license']['confirmed']);
        $this->assertFalse($data[$confirmed->id]['ifsg']['certificate']);
        $this->assertNull($data[$confirmed->id]['ifsg']['certificate_light']);

        // Goodie listing
        $this->config->set('goodie_type', GoodieType::Tshirt->value);
        $controller->setAuth($this->auth(['api', 'angeltypes.goodie.list', 'user.goodie.edit']));
        $data = collect(json_decode($controller->entriesByAngeltype($request)->getContent(), true)['data'])
            ->keyBy('user.id');
        $this->assertCount(3, $data);
        $this->assertEquals((bool) $confirmed->state->got_goodie, $data[$confirmed->id]['got_goodie']);
        $this->assertEquals($confirmed->personalData->shirt_size, $data[$confirmed->id]['shirt_size']);
    }

    public function testHasPermission(): void
    {
        $controller = new UsersController(new Response());
        $otherUser = (new Request())->withAttribute('user_id', '42');

        $controller->setAuth($this->auth(['user_shifts']));
        $this->assertTrue($controller->hasPermission($otherUser, 'user'));
        $this->assertTrue($controller->hasPermission($otherUser, 'worklogs'));
        $this->assertFalse($controller->hasPermission(new Request(), 'index'));
        $this->assertFalse($controller->hasPermission(new Request(), 'entriesByAngeltype'));

        $controller->setAuth($this->auth(['admin_user', 'angeltypes.view']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
        $this->assertTrue($controller->hasPermission(new Request(), 'entriesByAngeltype'));

        $controller->setAuth($this->auth(['api']));
        $this->assertTrue($controller->hasPermission(new Request(), 'index'));
    }

    protected function profileUser(): User
    {
        /** @var User $user */
        $user = User::factory()
            ->has(Contact::factory())
            ->has(PersonalData::factory())
            ->has(Settings::factory())
            ->has(State::factory(['arrival_date' => Carbon::now()->subDay(), 'active' => true]))
            ->create();
        $user->groups()->attach(Group::factory()->create());
        /** @var Shift $shift */
        $shift = Shift::factory()->create(['start' => Carbon::now()->addHour(), 'end' => Carbon::now()->addHours(2)]);
        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'user_id' => $user->id]);

        return $user;
    }

    /**
     * @param string[] $privileges
     */
    protected function userData(User $user, array $privileges, ?User $currentUser = null): array
    {
        $request = (new Request())->withAttribute('user_id', $currentUser ? 'self' : $user->id);

        $controller = new UsersController(new Response());
        $controller->setAuth($this->auth($privileges, $currentUser));

        $response = $controller->user($request);
        $this->validateApiResponse('/users/{id}', 'get', $response);

        return json_decode($response->getContent(), true)['data'];
    }

    /**
     * @param string[] $privileges
     */
    protected function auth(array $privileges, ?User $user = null): Authenticator
    {
        $auth = $this->createMock(Authenticator::class);
        $auth->method('user')->willReturn($user ?? User::factory()->create());
        $auth->method('can')->willReturnCallback(
            fn(array|string $abilities): bool => empty(array_diff((array) $abilities, $privileges))
        );

        return $auth;
    }
}
