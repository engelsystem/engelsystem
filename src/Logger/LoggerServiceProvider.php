<?php

namespace Engelsystem\Logger;

use Engelsystem\Container\ServiceProvider;
use Psr\Log\LoggerInterface;

class LoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $logger = $this->app->make(EngelsystemLogger::class);
        $this->app->instance('logger', $logger);

        $this->app->bind(LoggerInterface::class, 'logger');
        $this->app->bind(EngelsystemLogger::class, 'logger');
    }
}
