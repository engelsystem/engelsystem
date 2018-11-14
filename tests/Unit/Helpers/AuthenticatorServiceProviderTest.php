<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Application;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\AuthenticatorServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticatorServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\AuthenticatorServiceProvider::register()
     */
    public function testRegister()
    {
        $app = new Application();
        $app->bind(ServerRequestInterface::class, Request::class);

        $serviceProvider = new AuthenticatorServiceProvider($app);
        $serviceProvider->register();

        $this->assertInstanceOf(Authenticator::class, $app->get(Authenticator::class));
        $this->assertInstanceOf(Authenticator::class, $app->get('authenticator'));
        $this->assertInstanceOf(Authenticator::class, $app->get('auth'));
    }
}
