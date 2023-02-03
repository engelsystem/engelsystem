<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;

class AuthenticatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var Authenticator $authenticator */
        $authenticator = $this->app->make(Authenticator::class);
        $authenticator->setPasswordAlgorithm($config->get('password_algorithm'));
        $authenticator->setGuestRole($config->get('auth_guest_role', $authenticator->getGuestRole()));
        $authenticator->setDefaultRole($config->get('auth_default_role', $authenticator->getDefaultRole()));

        $this->app->instance(Authenticator::class, $authenticator);
        $this->app->instance('authenticator', $authenticator);
        $this->app->instance('auth', $authenticator);
    }
}
