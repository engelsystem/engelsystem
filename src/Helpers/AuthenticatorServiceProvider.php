<?php

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;

class AuthenticatorServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var Authenticator $authenticator */
        $authenticator = $this->app->make(Authenticator::class);
        $authenticator->setPasswordAlgorithm($config->get('password_algorithm'));
        $authenticator->setGuestRole($config->get('auth_guest_role', $authenticator->getGuestRole()));

        $this->app->instance(Authenticator::class, $authenticator);
        $this->app->instance('authenticator', $authenticator);
        $this->app->instance('auth', $authenticator);
    }
}
