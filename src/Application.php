<?php

namespace Engelsystem;

use Engelsystem\Container\Container;
use Psr\Container\ContainerInterface;

class Application extends Container
{
    /** @var string|null */
    protected $appPath = null;

    /**
     * Application constructor.
     *
     * @param string $appPath
     */
    public function __construct($appPath = null)
    {
        if (!is_null($appPath)) {
            $this->setAppPath($appPath);
        }

        $this->registerBaseBindings();
    }

    protected function registerBaseBindings()
    {
        static::setInstance($this);
        Container::setInstance($this);
        $this->instance('app', $this);
        $this->instance('container', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);
        $this->bind(ContainerInterface::class, Application::class);
    }

    /**
     * @param string $appPath
     * @return static
     */
    public function setAppPath($appPath)
    {
        $appPath = rtrim($appPath, DIRECTORY_SEPARATOR);

        $this->appPath = $appPath;
        $this->instance('path', $appPath);

        return $this;
    }

    /**
     * @return string|null
     */
    public function path()
    {
        return $this->appPath;
    }
}
