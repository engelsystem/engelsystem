<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Helpers\Stub\UserModelImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticatorTest extends ServiceProviderTest
{
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
            ->with('uid')
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

        $user->expects($this->once())
            ->method('save');

        $session->expects($this->exactly(2))
            ->method('get')
            ->with('uid')
            ->willReturn(42);
        $session->expects($this->once())
            ->method('remove')
            ->with('uid');

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
}
