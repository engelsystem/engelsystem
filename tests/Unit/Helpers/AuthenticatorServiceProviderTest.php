<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\AuthenticatorServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;

class AuthenticatorServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\AuthenticatorServiceProvider::register()
     */
    public function testRegister()
    {
        $app = new Application();

        $serviceProvider = new AuthenticatorServiceProvider($app);
        $serviceProvider->register();

        $this->assertInstanceOf(Authenticator::class, $app->get(Authenticator::class));
        $this->assertInstanceOf(Authenticator::class, $app->get('authenticator'));
    }
}
