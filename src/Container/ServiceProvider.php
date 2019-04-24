<?php

namespace Engelsystem\Container;

use Engelsystem\Application;

abstract class ServiceProvider
{
    /** @var Application */
    protected $app;

    /**
     * ServiceProvider constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register container bindings
     */
    public function register()
    {
    }

    /**
     * Called after other services had been registered
     */
    public function boot()
    {
    }
}
