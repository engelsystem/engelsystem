<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Helpers\Stub\UserModelImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticatorTest extends ServiceProviderTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\Authenticator::__construct(
     * @covers \Engelsystem\Helpers\Authenticator::user
     */
    public function testUser()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var UserModelImplementation|MockObject $userRepository */
        $userRepository = new UserModelImplementation();
        /** @var User|MockObject $user */
        $user = $this->createMock(User::class);

        $session->expects($this->exactly(3))
            ->method('get')
            ->with('user_id')
            ->willReturnOnConsecutiveCalls(
                null,
                42,
                1337
            );

        $auth = new Authenticator($request, $session, $userRepository);

        // Not in session
        $this->assertNull($auth->user());

        // Unknown user
        UserModelImplementation::$id = 42;
        $this->assertNull($auth->user());

        // User found
        UserModelImplementation::$id = 1337;
        UserModelImplementation::$user = $user;
        $this->assertEquals($user, $auth->user());

        // User cached
        UserModelImplementation::$id = null;
        UserModelImplementation::$user = null;
        $this->assertEquals($user, $auth->user());
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::apiUser
     */
    public function testApiUser()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var UserModelImplementation|MockObject $userRepository */
        $userRepository = new UserModelImplementation();
        /** @var User|MockObject $user */
        $user = $this->createMock(User::class);

        $request->expects($this->exactly(3))
            ->method('getQueryParams')
            ->with()
            ->willReturnOnConsecutiveCalls(
                [],
                ['api_key' => 'iMaNot3xiSt1nGAp1Key!'],
                ['foo_key' => 'SomeSecretApiKey']
            );

        /** @var Authenticator|MockObject $auth */
        $auth = new Authenticator($request, $session, $userRepository);

        // No key
        $this->assertNull($auth->apiUser());

        // Unknown user
        UserModelImplementation::$apiKey = 'iMaNot3xiSt1nGAp1Key!';
        $this->assertNull($auth->apiUser());

        // User found
        UserModelImplementation::$apiKey = 'SomeSecretApiKey';
        UserModelImplementation::$user = $user;
        $this->assertEquals($user, $auth->apiUser('foo_key'));

        // User cached
        UserModelImplementation::$apiKey = null;
        UserModelImplementation::$user = null;
        $this->assertEquals($user, $auth->apiUser());
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::can
     */
    public function testCan()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var UserModelImplementation|MockObject $userRepository */
        $userRepository = new UserModelImplementation();
        /** @var User|MockObject $user */
        $user = $this->createMock(User::class);

        $session->expects($this->once())
            ->method('get')
            ->with('user_id')
            ->willReturn(42);
        $session->expects($this->once())
            ->method('remove')
            ->with('user_id');

        /** @var Authenticator|MockObject $auth */
        $auth = $this->getMockBuilder(Authenticator::class)
            ->setConstructorArgs([$request, $session, $userRepository])
            ->setMethods(['getPermissionsByGroup', 'getPermissionsByUser', 'user'])
            ->getMock();
        $auth->expects($this->exactly(1))
            ->method('getPermissionsByGroup')
            ->with(-10)
            ->willReturn([]);
        $auth->expects($this->exactly(1))
            ->method('getPermissionsByUser')
            ->with($user)
            ->willReturn(['bar']);
        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls(null, $user);

        // No user, no permissions
        $this->assertFalse($auth->can('foo'));

        // User exists, has permissions
        $this->assertTrue($auth->can('bar'));

        // Permissions cached
        $this->assertTrue($auth->can('bar'));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::authenticate
     */
    public function testAuthenticate()
    {
        $this->initDatabase();

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        $userRepository = new User();

        (new User([
            'name'     => 'lorem',
            'password' => password_hash('testing', PASSWORD_DEFAULT),
            'email'    => 'lorem@foo.bar',
            'api_key'  => '',
        ]))->save();
        (new User([
            'name'     => 'ipsum',
            'password' => '',
            'email'    => 'ipsum@foo.bar',
            'api_key'  => '',
        ]))->save();

        $auth = new Authenticator($request, $session, $userRepository);
        $this->assertNull($auth->authenticate('not-existing', 'foo'));
        $this->assertNull($auth->authenticate('ipsum', 'wrong-password'));
        $this->assertInstanceOf(User::class, $auth->authenticate('lorem', 'testing'));
        $this->assertInstanceOf(User::class, $auth->authenticate('lorem@foo.bar', 'testing'));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::verifyPassword
     */
    public function testVerifyPassword()
    {
        $this->initDatabase();
        $password = password_hash('testing', PASSWORD_ARGON2I);
        $user = new User([
            'name'     => 'lorem',
            'password' => $password,
            'email'    => 'lorem@foo.bar',
            'api_key'  => '',
        ]);
        $user->save();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPassword'])
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
    public function testSetPassword()
    {
        $this->initDatabase();
        $user = new User([
            'name'     => 'ipsum',
            'password' => '',
            'email'    => 'ipsum@foo.bar',
            'api_key'  => '',
        ]);
        $user->save();

        $auth = $this->getAuthenticator();
        $auth->setPasswordAlgorithm(PASSWORD_ARGON2I);

        $auth->setPassword($user, 'FooBar');
        $this->assertTrue($user->isClean());

        $this->assertTrue(password_verify('FooBar', $user->password));
        $this->assertFalse(password_needs_rehash($user->password, PASSWORD_ARGON2I));
    }

    /**
     * @covers \Engelsystem\Helpers\Authenticator::setPasswordAlgorithm
     * @covers \Engelsystem\Helpers\Authenticator::getPasswordAlgorithm
     */
    public function testPasswordAlgorithm()
    {
        $auth = $this->getAuthenticator();

        $auth->setPasswordAlgorithm(PASSWORD_ARGON2I);
        $this->assertEquals(PASSWORD_ARGON2I, $auth->getPasswordAlgorithm());
    }

    /**
     * @return Authenticator
     */
    protected function getAuthenticator()
    {
        return new class extends Authenticator
        {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() { }
        };
    }
}
