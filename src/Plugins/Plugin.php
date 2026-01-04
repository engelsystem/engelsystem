<?php

declare(strict_types=1);

namespace Engelsystem\Plugins;

use FastRoute\RouteCollector;

abstract class Plugin
{
    protected string $name;
    protected string $pluginName;
    protected string $path;
    protected string $namespace;
    protected string $namespacePath;
    protected string $version;
    protected ?string $description;
    protected string $license;
    protected ?string $homepage;
    protected array $authors;
    protected array $extra;
    protected array $providers;
    protected array $middleware;
    protected array $eventHandlers;
    protected array $configOptions;
    protected array $routes;

    public function __construct(array $pluginInfo)
    {
        $this->name = $pluginInfo['name'];
        $this->pluginName = $pluginInfo['plugin_name'];
        $this->path = $pluginInfo['path'];
        $this->namespace = $pluginInfo['namespace'];
        $this->namespacePath = $pluginInfo['namespace_path'];
        $this->version = $pluginInfo['version'] ?? '0.0.0';
        $this->description = $pluginInfo['description'] ?? null;
        $this->license = implode(', ', (array) ($pluginInfo['license'] ?? 'proprietary'));
        $this->homepage = $pluginInfo['homepage'] ?? null;
        $this->authors = $pluginInfo['authors'] ?? [];
        $this->extra = $pluginInfo['extra'] ?? [];
        $this->providers = $this->extra['providers'] ?? [];
        $this->middleware = $this->extra['middleware'] ?? [];
        $this->eventHandlers = $this->extra['event_handlers'] ?? [];
        $this->configOptions = $this->extra['config_options'] ?? [];
        $this->routes = $this->extra['routes'] ?? [];
    }

    /**
     * On every application boot
     */
    public function boot(): void
    {
    }

    /**
     * After plugin installation
     */
    public function install(): void
    {
    }

    /**
     * Before plugin uninstall
     */
    public function uninstall(): void
    {
    }

    /**
     * After plugin update
     */
    public function update(string $from): void
    {
    }

    /**
     * After plugin enable
     */
    public function enable(): void
    {
    }

    /**
     * Before plugin disable
     */
    public function disable(): void
    {
    }

    public function loadRoutes(RouteCollector $collector): void
    {
        foreach ($this->getRoutes() as $route => $data) {
            $data = (array) $data;
            $collector->addRoute($data[1] ?? 'GET', $route, $data[0]);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getNamespacePath(): string
    {
        return $this->namespacePath;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getLicense(): string
    {
        return $this->license;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getEventHandlers(): array
    {
        return $this->eventHandlers;
    }

    public function getConfigOptions(): array
    {
        return $this->configOptions;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
