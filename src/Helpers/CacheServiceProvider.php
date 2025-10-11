<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('cache', Cache::class);
        $this->app->when(Cache::class)
            ->needs('$path')
            ->give(fn() => $this->app->get('path.cache'));
    }
}
