<?php

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class VersionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->when(Version::class)
            ->needs('$storage')
            ->give($this->app->get('path.storage.app'));
    }
}
