<?php

namespace Engelsystem;

use Engelsystem\Container\Container;
use Psr\Container\ContainerInterface;

class Application extends Container
{
    public function __construct()
    {
        $this->registerBaseBindings();
    }

    protected function registerBaseBindings()
    {
        self::setInstance($this);
        Container::setInstance($this);
        $this->instance('app', $this);
        $this->instance('container', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);
        $this->instance(ContainerInterface::class, $this);
    }
}
