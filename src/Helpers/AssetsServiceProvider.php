<?php

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->when(Assets::class)
            ->needs('$assetsPath')
            ->give($this->app->get('path.assets.public'));
    }
}
