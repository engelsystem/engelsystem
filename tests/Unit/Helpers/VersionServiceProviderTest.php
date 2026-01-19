<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Version;
use Engelsystem\Helpers\VersionServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(VersionServiceProvider::class, 'register')]
class VersionServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();
        $app->instance('path', '/tmp');
        $app->instance('path.storage.app', '/tmp');

        $serviceProvider = new VersionServiceProvider($app);
        $serviceProvider->register();

        $this->assertArrayHasKey(Version::class, $app->contextual);
    }
}
