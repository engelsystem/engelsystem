<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful;

use Engelsystem\Container\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public bool $registered = false;
    public bool $booted = false;

    public function register(): void
    {
        $this->registered = true;
    }

    public function boot(): void
    {
        $this->booted = true;
    }
}
