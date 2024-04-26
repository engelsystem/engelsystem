<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Models\Group;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Helpers\Stub\UserModelImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class AuthenticatorTest extends ServiceProviderTest
{
    use HasDatabase;

    protected static ?string $passwordHashTesting = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$passwordHashTesting = password_hash('testing', PASSWORD_ARGON2I, ['memory_cost' => 100]);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::user
     * @covers \Engelsystem\Helpers\Authenticator::__construct
     */
    public function testUserNotAuthorized(): void
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        /** @var UserModelImplementation|MockObject $userRepository */
        $userRepository = new UserModelImplementation();
        $this->app->instance('request', $request);

        $auth = new Authenticator($request, $session, $userRepository);
        $user = $auth->user();

        $this->assertNull($user);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::user
     * @covers \Engelsystem\Helpers\Authenticator::userFromSession
     */
    public function testUserViaFromSession(): void
    {
        $this->initDatabase();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());

        $session->set('user_id', 42);
        User::factory()->create(['id' => 42]);

        $auth = new Authenticator($request, $session, new User());
        $user = $auth->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(42, $user->id);

        // Cached in user()
        $user2 = $auth->user();
        $this->assertEquals($user, $user2);

        // Cached in userFromSession()
        $user3 = $auth->userFromSession();
        $this->assertEquals($user, $user3);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::user
     * @covers \Engelsystem\Helpers\Authenticator::userFromApi
     * @covers \Engelsystem\Helpers\Authenticator::userByHeaders
     */
    public function testUserViaFromApi(): void
    {
        $this->initDatabase();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());

        $request = $request->withHeader('Authorization', 'Bearer F00Bar');
        $request = $request->withAttribute('route-api-accessible', true);
        $this->app->instance('request', $request);
        User::factory()->create(['api_key' => 'F00Bar']);

        $auth = new Authenticator($request, $session, new User());
        $user = $auth->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('F00Bar', $user->api_key);

        // Cached in userFromApi()
        $user2 = $auth->userFromApi();
        $this->assertEquals($user, $user2);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::userFromSession
     */
    public function testUserFromSessionNotFound(): void
    {
        $this->initDatabase();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());

        $auth = new Authenticator($request, $session, new User());

        $user = $auth->userFromSession();
        $this->assertNull($user);

        $session->set('user_id', 42);
        $user2 = $auth->userFromSession();
        $this->assertNull($user2);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::userFromApi
     * @covers \Engelsystem\Helpers\Authenticator::userByQueryParam
     * @covers \Engelsystem\Helpers\Authenticator::userByApiKey
     */
    public function testUserFromApiByQueryParam(): void
    {
        $this->initDatabase();

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());

        $request = $request->withQueryParams(['key' => 'F00Bar']);

        $auth = new Authenticator($request, $session, new User());

        // User not found
        $user = $auth->userFromApi();
        $this->assertNull($user);

        // User exists
        User::factory()->create(['api_key' => 'F00Bar']);
        $user2 = $auth->userFromApi();
        $this->assertInstanceOf(User::class, $user2);
        $this->assertEquals('F00Bar', $user2->api_key);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::userByHeaders
     */
    public function testUserByHeaders(): void
    {
        $this->initDatabase();

        $request = new Request();
        $request = $request->withAttribute('route-api-accessible', true);
        $session = new Session(new MockArraySessionStorage());
        $this->app->instance('request', $request);

        $auth = new Authenticator($request, $session, new User());

        // Header not set
        $user = $auth->userFromApi();
        $this->assertNull($user);

        // User not found
        $request = $request->withHeader('x-api-key', 'SomeWrongKey');
        $auth = new Authenticator($request, $session, new User());
        $user = $auth->userFromApi();
        $this->assertNull($user);

        $request = $request->withHeader('x-api-key', 'F00Bar');
        $auth = new Authenticator($request, $session, new User());
        User::factory()->create(['api_key' => 'F00Bar']);
        $user = $auth->user();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('F00Bar', $user->api_key);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::userByHeaders
     */
    public function testUserByHeadersBearerTrimApiKey(): void
    {
        $this->initDatabase();

        $request = new Request();
        $request = $request->withAttribute('route-api-accessible', true);
        $session = new Session(new MockArraySessionStorage());
        $this->app->instance('request', $request);

        $request = $request->withHeader('authorization', 'bearer  F00Bar ');
        $auth = new Authenticator($request, $session, new User());
        User::factory()->create(['api_key' => 'F00Bar']);
        $user = $auth->user();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('F00Bar', $user->api_key);
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::resetApiKey
     */
    public function testResetApiKey(): void
    {
        $this->initDatabase();

        $user = User::factory()->create();
        $oldKey = $user->api_key;

        $auth = new Authenticator(new Request(), new Session(new MockArraySessionStorage()), new User());
        $auth->resetApiKey($user);

        $updatedUser = User::all()->last();
        $newApiKey = $updatedUser->api_key;

        $this->assertNotEquals($oldKey, $newApiKey);
        $this->assertTrue(Str::isAscii($newApiKey));
        $this->assertEquals(64, Str::length($newApiKey));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::can
     * @covers \Engelsystem\Helpers\Authenticator::loadPermissions
     * @covers \Engelsystem\Helpers\Authenticator::isApiRequest
     */
    public function testCan(): void
    {
        $this->initDatabase();

        $request = new Request();
        $this->app->instance('request', $request);
        $session = new Session(new MockArraySessionStorage());
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Group $group */
        $group = Group::factory()->create();
        /** @var Privilege $privilege */
        $privilege = Privilege::factory()->create(['name' => 'bar']);

        $user->groups()->attach($group);
        $group->privileges()->attach($privilege);

        $auth = new Authenticator($request, $session, new User());
        $session->set('user_id', $user->id);
        // User exists, has permissions
        $this->assertTrue($auth->can('bar'));

        // Permissions cached
        $this->assertTrue($auth->can(['bar']));

        // Can not
        $this->assertFalse($auth->can(['nope']));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::can
     * @covers \Engelsystem\Helpers\Authenticator::loadPermissions
     */
    public function testCanUnauthorized(): void
    {
        $this->initDatabase();

        $request = new Request();
        $this->app->instance('request', $request);
        $session = new Session(new MockArraySessionStorage());

        $auth = new Authenticator($request, $session, new User());
        $session->set('user_id', 42);

        // No user, no permissions
        $this->assertFalse($auth->can('foo'));
        // Old/invalid user id got removed
        $this->assertNull($session->get('user_id'));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::canAny
     */
    public function testCanAny(): void
    {
        $this->initDatabase();

        $request = new Request();
        $this->app->instance('request', $request);
        $session = new Session(new MockArraySessionStorage());
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Group $group */
        $group = Group::factory()->create();
        /** @var Privilege $privilege */
        $privilege = Privilege::factory()->create(['name' => 'bar']);

        $user->groups()->attach($group);
        $group->privileges()->attach($privilege);

        $auth = new Authenticator($request, $session, new User());
        $session->set('user_id', $user->id);

        $this->assertTrue($auth->canAny('bar'));
        $this->assertTrue($auth->canAny(['foo', 'bar', 'baz']));
        $this->assertFalse($auth->canAny(['lorem', 'ipsum']));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::authenticate
     */
    public function testAuthenticate(): void
    {
        $this->initDatabase();

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        $userRepository = new User();

        User::factory([
            'name'     => 'lorem',
            'password' => self::$passwordHashTesting,
            'email'    => 'lorem@foo.bar',
        ])->create();
        User::factory([
            'name'     => 'ipsum',
            'password' => '',
        ])->create();

        $auth = new Authenticator($request, $session, $userRepository);
        $this->assertNull($auth->authenticate('not-existing', 'foo'));
        $this->assertNull($auth->authenticate('ipsum', 'wrong-password'));
        $this->assertInstanceOf(User::class, $auth->authenticate('lorem', 'testing'));
        $this->assertInstanceOf(User::class, $auth->authenticate('lorem@foo.bar', 'testing'));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::verifyPassword
     */
    public function testVerifyPassword(): void
    {
        $this->initDatabase();
        $password = self::$passwordHashTesting;

        /** @var User $user */
        $user = User::factory([
            'name'     => 'lorem',
            'password' => $password,
        ])->create();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPassword'])
            ->getMock();

        $auth->expects($this->once())
            ->method('setPassword')
            ->with($user, 'testing');
        $auth->setPasswordAlgorithm(PASSWORD_BCRYPT);

        $this->assertFalse($auth->verifyPassword($user, 'randomStuff'));
        $this->assertTrue($auth->verifyPassword($user, 'testing'));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::setPassword
     */
    public function testSetPassword(): void
    {
        $this->initDatabase();
        /** @var User $user */
        $user = User::factory([
            'name'     => 'ipsum',
            'password' => '',
        ])->create();
        $user->save();

        $auth = $this->getAuthenticator();
        $auth->setPasswordAlgorithm(PASSWORD_BCRYPT);

        $auth->setPassword($user, 'FooBar');
        $this->assertTrue($user->isClean());

        $this->assertTrue(password_verify('FooBar', $user->password));
        $this->assertFalse(password_needs_rehash($user->password, PASSWORD_BCRYPT));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::setPasswordAlgorithm
     * @covers \Engelsystem\Helpers\Authenticator::getPasswordAlgorithm
     */
    public function testPasswordAlgorithm(): void
    {
        $auth = $this->getAuthenticator();

        $auth->setPasswordAlgorithm(PASSWORD_ARGON2I);
        $this->assertEquals(PASSWORD_ARGON2I, $auth->getPasswordAlgorithm());
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::setDefaultRole
     * @covers \Engelsystem\Helpers\Authenticator::getDefaultRole
     */
    public function testDefaultRole(): void
    {
        $auth = $this->getAuthenticator();

        $auth->setDefaultRole(1337);
        $this->assertEquals(1337, $auth->getDefaultRole());
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::setGuestRole
     * @covers \Engelsystem\Helpers\Authenticator::getGuestRole
     */
    public function testGuestRole(): void
    {
        $auth = $this->getAuthenticator();

        $auth->setGuestRole(42);
        $this->assertEquals(42, $auth->getGuestRole());
    }

    protected function getAuthenticator(): Authenticator
    {
        return new class extends Authenticator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct()
            {
            }
        };
    }
}
