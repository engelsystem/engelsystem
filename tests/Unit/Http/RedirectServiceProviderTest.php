<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Http\RedirectServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;

class RedirectServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\RedirectServiceProvider::register
     */
    public function testRegister(): void
    {
        $app = new Application();

        $serviceProvider = new RedirectServiceProvider($app);
        $serviceProvider->register();

        $this->assertTrue($app->has('redirect'));
    }
}
