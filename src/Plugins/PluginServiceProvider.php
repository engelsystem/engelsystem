<?php

declare(strict_types=1);

namespace Engelsystem\Plugins;

use DirectoryIterator;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Models\Plugin;
use Engelsystem\Plugins\Plugin as AbstractPlugin;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $pluginPath = $this->app->get('path.plugins');
        try {
            $enabledPlugins = Plugin::whereEnabled(true)->get();
        } catch (QueryException) {
            return;
        }
        /** @var DirectoryIterator $fileInfo */
        foreach (new DirectoryIterator($pluginPath) as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            $pluginName = $fileInfo->getFilename();
            $pluginPath = $fileInfo->getPath() . '/' . $pluginName . '/';
            $isEnabled = $enabledPlugins->filter(fn(Plugin $p) => $p->name === $pluginName)->isNotEmpty();

            $this->addPlugin($pluginName, $pluginPath, $isEnabled);
        }
    }

    public function boot(): void
    {
        /** @var AbstractPlugin $plugin */
        foreach ($this->app->tagged('plugin') as $plugin) {
            foreach ($plugin->getProviders() as $providerName) {
                /** @var ServiceProvider $provider */
                $provider = $this->app->get($providerName);
                $provider->boot();
            }

            $plugin->boot();
        }
    }

    public function addPlugin(string $pluginName, string $pluginPath, bool $isEnabled): void
    {
        $composerFile = $pluginPath . 'composer.json';

        if (!is_readable($composerFile)) {
            return;
        }

        $pluginInfo = json_decode(file_get_contents($composerFile), true);
        if (!$pluginInfo || empty($pluginInfo['name']) || ($pluginInfo['type'] ?? null) !== 'engelsystem-plugin') {
            return;
        }

        list($pluginNamespace, $pluginNamespacePath) = $this->getNamespace(
            $pluginInfo['autoload'] ?? [],
            $pluginPath
        );
        if (!$pluginNamespace) {
            return;
        }

        $pluginInfo['plugin_name'] = $pluginName;
        $pluginInfo['path'] = $pluginPath;
        $pluginInfo['namespace'] = $pluginNamespace;
        $pluginInfo['namespace_path'] = $pluginNamespacePath;
        if ($isEnabled) {
            $this->registerNamespaces($pluginInfo['autoload'] ?? [], $pluginPath);
        }

        $plugin = $this->registerPluginInstance($pluginNamespace, $pluginName, $pluginInfo, $pluginPath, $isEnabled);

        if ($isEnabled) {
            $this->registerPluginProviders($plugin);
            $this->registerPluginMiddleware($plugin);
            $this->registerPluginEventHandlers($plugin);
            $this->registerPluginConfigOptions($plugin);
        }
    }

    protected function getNamespace(array $autoload, string $pluginPath): ?array
    {
        foreach ($autoload['psr-4'] ?? [] as $namespace => $path) {
            $namespace = rtrim($namespace, '\\') . '\\';
            $path = rtrim($pluginPath . $path, '/') . '/';
            return [$namespace, $path];
        }

        return null;
    }

    protected function registerNamespaces(array $autoload, string $pluginPath): void
    {
        foreach ($autoload['psr-4'] ?? [] as $namespace => $path) {
            $namespace = rtrim($namespace, '\\') . '\\';
            $path = $pluginPath . rtrim($path, '/') . '/';

            spl_autoload_register(function (string $className) use ($namespace, $path): void {
                if (!Str::startsWith($className, $namespace)) {
                    return;
                }

                $className = Str::substr($className, Str::length($namespace));
                $file = $path . str_replace('\\', '/', $className) . '.php';
                if (!file_exists($file)) {
                    return;
                }

                require_once $file;
            });
        }
    }

    protected function registerPluginInstance(
        string $pluginNamespace,
        string $pluginName,
        array $pluginInfo,
        string $pluginPath,
        bool $isEnabled
    ): AbstractPlugin {
        $namespacedPlugin = $pluginNamespace . $pluginName;
        if ($this->app->bound($namespacedPlugin)) {
            // This is a rebind, reset previous singleton instance
            $this->app->singleton($namespacedPlugin);
        }

        if (!$isEnabled) {
            $this->app->alias(DisabledPlugin::class, $namespacedPlugin);
        }

        $plugin = $this->app->make($namespacedPlugin, ['pluginInfo' => $pluginInfo]);

        // Set and tag plugin instance
        $this->app->singleton($namespacedPlugin, fn() => $plugin);
        $this->app->alias($namespacedPlugin, $pluginName);
        $this->app->tag($pluginName, $isEnabled ? 'plugin' : 'plugin.disabled');

        // Set plugin path to be available by tag
        $this->app->instance($pluginPath, $pluginPath);
        $this->app->tag($pluginPath, $isEnabled ? 'plugin.path' : 'plugin.path.disabled');

        return $plugin;
    }

    protected function registerPluginProviders(AbstractPlugin $plugin): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        $providers = $config->get('providers');
        foreach ($plugin->getProviders() as $providerName) {
            $providers[] = $providerName;
            $this->app->singleton($providerName);
            /** @var ServiceProvider $provider */
            $provider = $this->app->make($providerName);
            $provider->register();
        }
        $config->set('providers', $providers);
    }

    protected function registerPluginMiddleware(AbstractPlugin $plugin): void
    {
        foreach ($plugin->getMiddleware() as $middleware) {
            $this->app->tag($middleware, 'plugin.middleware');
        }
    }

    protected function registerPluginEventHandlers(AbstractPlugin $plugin): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->app->get(EventDispatcher::class);
        foreach ($plugin->getEventHandlers() as $event => $handlers) {
            foreach ((array) $handlers as $handler) {
                $dispatcher->listen($event, $handler);
            }
        }
    }

    protected function registerPluginConfigOptions(AbstractPlugin $plugin): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        $configOptions = $config->get('config_options');
        foreach ($plugin->getConfigOptions() as $type => $options) {
            $configOptions[$type] = array_merge_recursive($configOptions[$type] ?? [], $options);
        }
        $config->set('config_options', $configOptions);
    }
}
