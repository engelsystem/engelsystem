<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Twig\Extensions\Authentication;
use PHPUnit\Framework\MockObject\MockObject;

class AuthenticationTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::getFunctions
     */
    public function testGetFunctions()
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        $extension = new Authentication($auth);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('is_user', [$extension, 'isAuthenticated'], $functions);
        $this->assertExtensionExists('is_guest', [$extension, 'isGuest'], $functions);
        $this->assertExtensionExists('has_permission_to', [$auth, 'can'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::isAuthenticated
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::isGuest
     */
    public function testIsAuthenticated()
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $user = new User();

        $auth->expects($this->exactly(4))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                null,
                $user,
                $user
            );

        $extension = new Authentication($auth);

        $this->assertFalse($extension->isAuthenticated());
        $this->assertTrue($extension->isGuest());

        $this->assertTrue($extension->isAuthenticated());
        $this->assertFalse($extension->isGuest());
    }
}
