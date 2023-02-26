<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Session;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SessionTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Session::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Session::getFunctions
     */
    public function testGetGlobals(): void
    {
        $session = new SymfonySession(new MockArraySessionStorage());

        $extension = new Session($session);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('session_get', [$session, 'get'], $functions);
        $this->assertExtensionExists('session_set', [$session, 'set'], $functions);
        $this->assertExtensionExists('session_pop', [$extension, 'sessionPop'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Session::sessionPop
     */
    public function testSessionPop(): void
    {
        $session = new SymfonySession(new MockArraySessionStorage());
        $session->set('test', 'value');

        $extension = new Session($session);

        $result = $extension->sessionPop('test');
        $this->assertEquals('value', $result);
        $this->assertFalse($session->has('test'));

        $result = $extension->sessionPop('foo', 'default value');
        $this->assertEquals('default value', $result);
    }
}
