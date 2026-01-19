<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Twig\Extensions\Authentication;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Authentication::class, '__construct')]
#[CoversMethod(Authentication::class, 'getFunctions')]
#[CoversMethod(Authentication::class, 'isAuthenticated')]
#[CoversMethod(Authentication::class, 'isGuest')]
class AuthenticationTest extends ExtensionTestCase
{
    use HasDatabase;

    public function testGetFunctions(): void
    {
        $auth = $this->createStub(Authenticator::class);

        $extension = new Authentication($auth);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('is_user', [$extension, 'isAuthenticated'], $functions);
        $this->assertExtensionExists('is_guest', [$extension, 'isGuest'], $functions);
        $this->assertExtensionExists('can', [$auth, 'can'], $functions);
        $this->assertExtensionExists('canAny', [$auth, 'canAny'], $functions);
    }

    public function testIsAuthenticated(): void
    {
        $this->initDatabase();
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
