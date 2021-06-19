<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

class GlobalsTest extends ExtensionTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobals
     */
    public function testGetGlobals()
    {
        $this->initDatabase();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        $theme = ['name' => 'Testtheme', 'navbar_classes' => 'something'];
        $theme2 = ['name' => 'Bar'];
        $user = new User(['name' => '', 'email' => '', 'password' => '', 'api_key' => '']);
        $userSettings = new Settings(['theme' => 42, 'language' => '']);
        $config = new Config(['theme' => 23, 'themes' => [42 => $theme, 23 => $theme2]]);

        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                $user
            );

        $user->save();

        $userSettings->user()
            ->associate($user)
            ->save();

        $this->app->instance('config', $config);

        $extension = new Globals($auth, $request);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', [], $globals);
        $this->assertGlobalsExists('request', $request, $globals);
        $this->assertGlobalsExists('themeId', 23, $globals);
        $this->assertGlobalsExists('theme', $theme2, $globals);

        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', $user, $globals);
        $this->assertGlobalsExists('themeId', 42, $globals);
        $this->assertGlobalsExists('theme', $theme, $globals);
    }
}
