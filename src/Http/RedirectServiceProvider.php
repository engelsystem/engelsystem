<?php

declare(strict_types=1);

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;

class RedirectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('redirect', Redirector::class);
    }
}
