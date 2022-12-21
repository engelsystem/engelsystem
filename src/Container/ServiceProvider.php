<?php

namespace Engelsystem\Container;

use Engelsystem\Application;

abstract class ServiceProvider
{
    /**
     * ServiceProvider constructor.
     */
    public function __construct(protected Application $app)
    {
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
