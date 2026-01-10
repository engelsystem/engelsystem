<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Cache;
use Engelsystem\Helpers\CacheServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(CacheServiceProvider::class, 'register')]
class CacheServiceProviderTest extends ServiceProviderTestCase
{
    public function testRegister(): void
    {
        $app = new Application();
        $app->instance('path.cache', '/tmp');

        $serviceProvider = new CacheServiceProvider($app);
        $serviceProvider->register();

        $this->assertTrue($app->bound('cache'));
        $this->assertArrayHasKey(Cache::class, $app->contextual);

        $cache = $app->get(Cache::class);
        $this->assertInstanceOf(Cache::class, $cache);
    }
}
