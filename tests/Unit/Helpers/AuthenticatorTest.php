<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Helpers\Stub\UserModelImplementation;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticatorTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Authenticator::__construct(
     * @covers \Engelsystem\Helpers\Authenticator::user
     */
    public function testUser()
    {
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

        $auth = new Authenticator($session, $userRepository);

        // Not in session
        $this->assertEquals(null, $auth->user());

        // Unknown user
        UserModelImplementation::$id = 42;
        $this->assertEquals(null, $auth->user());

        // User found
        UserModelImplementation::$id = 1337;
        UserModelImplementation::$user = $user;
        $this->assertEquals($user, $auth->user());

        // User cached
        UserModelImplementation::$id = null;
        UserModelImplementation::$user = null;
        $this->assertEquals($user, $auth->user());
    }
}
