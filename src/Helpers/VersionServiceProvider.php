<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Container\ServiceProvider;

class VersionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(Version::class)
            ->needs('$storage')
            ->give($this->app->get('path.storage.app'));
    }
}
