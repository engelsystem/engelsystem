<?php

namespace Engelsystem\Container;

use Engelsystem\Application;

abstract class ServiceProvider
{
    protected Application $app;

    /**
     * ServiceProvider constructor.
     *
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register container bindings
     */
    public function register(): void
    {
    }

    /**
     * Called after other services had been registered
     */
    public function boot(): void
    {
    }
}
