<?php

declare(strict_types=1);

namespace Demo\Plugin;

use Engelsystem\Container\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app
            ->when(Middleware::class)
            ->needs('$status')
            ->give('activated!');
    }
}
