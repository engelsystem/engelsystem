<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
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
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        $user = User::factory()->make();

        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                $user
            );

        $extension = new Globals($auth, $request);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', [], $globals);
        $this->assertGlobalsExists('request', $request, $globals);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', $user, $globals);
    }
}
