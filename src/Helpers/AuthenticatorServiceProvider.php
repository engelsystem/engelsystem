<?php

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class AuthenticatorServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Authenticator $authenticator */
        $authenticator = $this->app->make(Authenticator::class);

        $this->app->instance(Authenticator::class, $authenticator);
        $this->app->instance('authenticator', $authenticator);
        $this->app->instance('auth', $authenticator);
    }
}
