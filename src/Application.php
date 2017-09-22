<?php

namespace Engelsystem;

use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Container\ServiceProvider;
use Psr\Container\ContainerInterface;

class Application extends Container
{
    /** @var string|null */
    protected $appPath = null;

    /** @var bool */
    protected $isBootstrapped = false;

    /**
     * Registered service providers
     *
     * @var array
     */
    protected $serviceProviders = [];

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
     * @param string|ServiceProvider $provider
     * @return ServiceProvider
     */
    public function register($provider)
    {
        if (is_string($provider)) {
            $provider = $this->get($provider);
        }

        $this->serviceProviders[] = $provider;

        $provider->register();

        if ($this->isBootstrapped) {
            $this->call([$provider, 'boot']);
        }

        return $provider;
    }

    /**
     * Boot service providers
     *
     * @param Config|null $config
     */
    public function bootstrap(Config $config = null)
    {
        if ($this->isBootstrapped) {
            return;
        }

        if ($config instanceof Config) {
            foreach ($config->get('providers', []) as $provider) {
                $this->register($provider);
            }
        }

        foreach ($this->serviceProviders as $provider) {
            $this->call([$provider, 'boot']);
        }

        $this->isBootstrapped = true;
    }

    protected function registerPaths()
    {
        $appPath = $this->appPath;

        $this->instance('path', $appPath);
        $this->instance('path.config', $appPath . DIRECTORY_SEPARATOR . 'config');
        $this->instance('path.lang', $appPath . DIRECTORY_SEPARATOR . 'locale');
    }

    /**
     * Set app base path
     *
     * @param string $appPath
     * @return static
     */
    public function setAppPath($appPath)
    {
        $appPath = realpath($appPath);
        $appPath = rtrim($appPath, DIRECTORY_SEPARATOR);

        $this->appPath = $appPath;

        $this->registerPaths();

        return $this;
    }

    /**
     * @return string|null
     */
    public function path()
    {
        return $this->appPath;
    }

    /**
     * @return bool
     */
    public function isBooted()
    {
        return $this->isBootstrapped;
    }
}
