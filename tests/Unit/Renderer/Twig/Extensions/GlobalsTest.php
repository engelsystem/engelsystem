<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Request;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

class GlobalsTest extends ExtensionTest
{
    use HasDatabase;

    public static function setUpBeforeClass(): void
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2023-08-15'));
    }

    public static function tearDownAfterClass(): void
    {
        Carbon::setTestNow();
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobals
     * @covers \Engelsystem\Renderer\Twig\Extensions\Globals::getGlobalValues
     */
    public function testGetGlobals(): void
    {
        $this->initDatabase();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $request = new Request();
        $theme = ['name' => 'Testtheme', 'navbar_classes' => 'something'];
        $theme2 = ['name' => 'Bar'];
        $theme3 = ['name' => 'Lorem'];
        /** @var User $user */
        $user = User::factory()
            ->has(Settings::factory(['theme' => 42]))
            ->create();
        $config = new Config(
            [
                'event_start' => Carbon::createFromFormat('Y-m-d', '2023-08-13'),
                'enable_day_of_event' => true,
                'theme'  => 23,
                'themes' => [
                    42   => $theme,
                    23   => $theme2,
                    1337 => $theme3,
                ],
            ]
        );

        $auth->expects($this->exactly(4))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                $user,
                $user,
                null
            );

        $this->app->instance('config', $config);

        $extension = new Globals($auth, $request);
        $globals = $extension->getGlobals();

        $this->assertGlobalsExists('day_of_event', 3, $globals);

        // No user
        $this->assertGlobalsExists('user', [], $globals);
        $this->assertGlobalsExists('user_messages', null, $globals);
        $this->assertGlobalsExists('request', $request, $globals);
        $this->assertGlobalsExists('themeId', 23, $globals);
        $this->assertGlobalsExists('theme', $theme2, $globals);

        // User
        $extension = new Globals($auth, $request);
        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', $user, $globals);
        $this->assertGlobalsExists('user_messages', 0, $globals);
        $this->assertGlobalsExists('themeId', 42, $globals);
        $this->assertGlobalsExists('theme', $theme, $globals);

        // User with not available theme configured
        $extension = new Globals($auth, $request);
        $user->settings->theme = 9999;
        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('themeId', 42, $globals);

        // Request query parameter
        $extension = new Globals($auth, $request);
        $request->query->set('theme', 1337);
        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('user', [], $globals);
        $this->assertGlobalsExists('themeId', 1337, $globals);
        $this->assertGlobalsExists('theme', $theme3, $globals);

        // Second retrieval is loaded directly
        $globals = $extension->getGlobals();
        $this->assertGlobalsExists('themeId', 1337, $globals);
    }
}
