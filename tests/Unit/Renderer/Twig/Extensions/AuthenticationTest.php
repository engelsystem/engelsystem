<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Authentication;

class AuthenticationTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::getFunctions
     */
    public function testGetFunctions()
    {
        $extension = new Authentication();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('is_user', [$extension, 'isAuthenticated'], $functions);
        $this->assertExtensionExists('is_guest', [$extension, 'isGuest'], $functions);
        $this->assertExtensionExists('has_permission_to', [$extension, 'checkAuth'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::isAuthenticated
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::isGuest
     */
    public function testIsAuthenticated()
    {
        global $user;
        $user = [];

        $extension = new Authentication();

        $this->assertFalse($extension->isAuthenticated());
        $this->assertTrue($extension->isGuest());

        $user = ['lorem' => 'ipsum'];
        $this->assertTrue($extension->isAuthenticated());
        $this->assertFalse($extension->isGuest());
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Authentication::checkAuth
     */
    public function testCheckAuth()
    {
        global $privileges;
        $privileges = [];

        $extension = new Authentication();

        $this->assertFalse($extension->checkAuth('foo.bar'));

        $privileges = ['foo.bar'];
        $this->assertTrue($extension->checkAuth('foo.bar'));
    }
}
