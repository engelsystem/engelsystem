<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Session;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class SessionTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Session::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Session::getFunctions
     */
    public function testGetGlobals()
    {
        /** @var SymfonySession|MockObject $session */
        $session = $this->createMock(SymfonySession::class);

        $extension = new Session($session);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('session_get', [$session, 'get'], $functions);
    }
}
