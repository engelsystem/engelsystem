<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use PHPUnit\Framework\MockObject\MockObject;

class GlobalsTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobals
     */
    public function testGetGlobals()
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $user = new User();

        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                $user
            );

        $extension = new Globals($auth);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', [], $globals);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', $user, $globals);
    }
}
