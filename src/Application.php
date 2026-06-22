<?php

declare(strict_types=1);

namespace Engelsystem;

use Engelsystem\Config\Config;
use Engelsystem\Container\Container;
use Engelsystem\Container\ServiceProvider;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\Container as IlluminateContainerContract;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application extends Container
{
    protected ?string $appPath = null;

    protected bool $isBootstrapped = false;

    /** @var MiddlewareInterface[]|string[] */
    protected array $middleware;

    /**
     * Registered service providers
     */
    protected array $serviceProviders = [];

    /**
     * Application constructor.
     */
    public function __construct(?string $appPath = null)
    {
        if (!is_null($appPath)) {
            $this->setAppPath($appPath);
        }

        $this->registerBaseBindings();
    }

    protected function registerBaseBindings(): void
    {
        static::setInstance($this);
        Container::setInstance($this);
        $this->instance('app', $this);
        $this->instance('container', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);
        $this->instance(IlluminateContainer::class, $this);
        $this->instance(IlluminateContainerContract::class, $this);
        $this->bind(ContainerInterface::class, self::class);
    }

    public function register(string|ServiceProvider $provider): ServiceProvider
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
     */
    public function bootstrap(?Config $config = null): void
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

    protected function registerPaths(): void
    {
        $appPath = $this->appPath;

        $this->instance('path', $appPath);
        $this->instance('path.config', $appPath . DIRECTORY_SEPARATOR . 'config');
        $this->instance('path.resources', $appPath . DIRECTORY_SEPARATOR . 'resources');
        $this->instance('path.resources.api', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'api');
        $this->instance('path.public', $appPath . DIRECTORY_SEPARATOR . 'public');
        $this->instance('path.assets', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'assets');
        $this->instance('path.assets.public', $this->get('path.public') . DIRECTORY_SEPARATOR . 'assets');
        $this->instance('path.lang', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'lang');
        $this->instance('path.views', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'views');
        $this->instance('path.storage', $appPath . DIRECTORY_SEPARATOR . 'storage');
        $this->instance('path.storage.app', $this->get('path.storage') . DIRECTORY_SEPARATOR . 'app');
        $this->instance('path.cache', $this->get('path.storage') . DIRECTORY_SEPARATOR . 'cache');
        $this->instance('path.cache.routes', $this->get('path.cache') . DIRECTORY_SEPARATOR . 'routes.cache.php');
        $this->instance('path.cache.views', $this->get('path.cache') . DIRECTORY_SEPARATOR . 'views');
        $this->instance('path.plugins', $this->get('path.resources') . DIRECTORY_SEPARATOR . 'plugins');
    }

    /**
     * Set app base path
     */
    public function setAppPath(string $appPath): static
    {
        $appPath = realpath($appPath);
        $appPath = rtrim($appPath, DIRECTORY_SEPARATOR);

        $this->appPath = $appPath;

        $this->registerPaths();

        return $this;
    }

    public function path(): ?string
    {
        return $this->appPath;
    }

    public function isBooted(): bool
    {
        return $this->isBootstrapped;
    }

    /**
     * @return MiddlewareInterface[]|string[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
