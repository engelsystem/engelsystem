<?php

namespace Engelsystem;

use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Container\ServiceProvider;
use Illuminate\Container\Container as IlluminateContainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application extends Container
{
    /** @var string|null */
    protected $appPath = null;

    /** @var bool */
    protected $isBootstrapped = false;

    /** @var MiddlewareInterface[]|string[] */
    protected $middleware;

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
        $this->instance(IlluminateContainer::class, $this);
        $this->bind(ContainerInterface::class, self::class);
    }

    /**
     * @param string|ServiceProvider $provider
     * @return ServiceProvider
     */
    public function register($provider)
    {
        if (is_string($provider)) {
            $provider = $this->make($provider);
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

            $this->middleware = $config->get('middleware', []);
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
        $this->instance('path.resources', $appPath . DIRECTORY_SEPARATOR . 'resources');
        $this->instance('path.assets', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'assets');
        $this->instance('path.lang', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'lang');
        $this->instance('path.views', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'views');
        $this->instance('path.storage', $appPath . DIRECTORY_SEPARATOR . 'storage');
        $this->instance('path.cache', $this->get('path.storage') . DIRECTORY_SEPARATOR . 'cache');
        $this->instance('path.cache.routes', $this->get('path.cache') . DIRECTORY_SEPARATOR . 'routes.cache.php');
        $this->instance('path.cache.views', $this->get('path.cache') . DIRECTORY_SEPARATOR . 'views');
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

    /**
     * @return MiddlewareInterface[]|string[]
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
