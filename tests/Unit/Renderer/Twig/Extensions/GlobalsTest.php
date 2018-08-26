<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Globals;

class GlobalsTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobals
     */
    public function testGetGlobals()
    {
        $extension = new Globals();
        $globals = $extension->getGlobals();

        $this->assertGlobalsExists('user', [], $globals);

        global $user;
        $user['foo'] = 'bar';

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', ['foo' => 'bar'], $globals);
    }
}
