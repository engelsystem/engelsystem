<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(Assets::class)
            ->needs('$assetsPath')
            ->give($this->app->get('path.assets.public'));
    }
}
