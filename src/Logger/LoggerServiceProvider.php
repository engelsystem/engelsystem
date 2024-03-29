<?php

declare(strict_types=1);

namespace Engelsystem\Logger;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\Authenticator;
use Psr\Log\LoggerInterface;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $logger = $this->app->make(UserAwareLogger::class);
        $this->app->instance('logger', $logger);

        $this->app->bind(LoggerInterface::class, 'logger');
        $this->app->bind(Logger::class, 'logger');
        $this->app->bind(UserAwareLogger::class, 'logger');
    }

    public function boot(): void
    {
        /** @var UserAwareLogger $logger */
        $logger = $this->app->get(UserAwareLogger::class);
        /** @var Authenticator $auth */
        $auth = $this->app->get(Authenticator::class);

        $logger->setAuth($auth);
    }
}
