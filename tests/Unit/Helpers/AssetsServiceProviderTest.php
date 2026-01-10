<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Assets;
use Engelsystem\Helpers\AssetsServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(AssetsServiceProvider::class, 'register')]
class AssetsServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();
        $app->instance('path.assets.public', '/tmp');

        $serviceProvider = new AssetsServiceProvider($app);
        $serviceProvider->register();

        $this->assertArrayHasKey(Assets::class, $app->contextual);
    }
}
