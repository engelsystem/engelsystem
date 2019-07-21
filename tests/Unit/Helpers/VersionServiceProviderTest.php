<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Version;
use Engelsystem\Helpers\VersionServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;

class VersionServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\VersionServiceProvider::register
     */
    public function testRegister()
    {
        $app = new Application();
        $app->instance('path.storage.app', '/tmp');

        $serviceProvider = new VersionServiceProvider($app);
        $serviceProvider->register();

        $this->assertArrayHasKey(Version::class, $app->contextual);
    }
}
