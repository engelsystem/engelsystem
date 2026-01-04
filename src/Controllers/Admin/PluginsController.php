<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Application;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Database\Migration\Direction;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Plugin;
use Engelsystem\Plugins\Plugin as AbstractPlugin;
use Engelsystem\Plugins\PluginServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class PluginsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'plugin.edit',
    ];

    public function __construct(
        protected Application $app,
        protected LoggerInterface $log,
        protected Migrate $migration,
        protected Plugin $plugin,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function list(): Response
    {
        $plugins = $this->getAllPlugins();

        $installedPlugins = $this->plugin->all()->keyBy('name');

        return $this->response->withView(
            'admin/plugin.twig',
            [
                'plugins' => $plugins,
                'installedPlugins' => $installedPlugins,
            ]
        );
    }

    public function updateState(Request $request): Response
    {
        $name = (string) $request->getAttribute('plugin');
        $plugins = $this->getAllPlugins();
        /** @var AbstractPlugin $plugin */
        $plugin = $plugins->get($name);
        if (!$plugin) {
            throw new HttpNotFound();
        }

        $data = $this->validate($request, [
            'action' => 'required|in:install,uninstall,update,enable,disable',
        ]);
        /** @var Plugin $model */
        $model = $this->plugin->firstOrNew(
            ['name' => $plugin->getPluginName()],
            ['version' => $plugin->getVersion(), 'enabled' => true],
        );
        $baseDir = $plugin->getNamespacePath() . 'Migrations';
        if (is_dir($baseDir)) {
            $this->migration->setNamespace($plugin->getNamespace() . 'Migrations\\');
            $this->migration->setOutput(function (string $message): void {
                if (Str::startsWith($message, 'Skipping ')) {
                    return;
                }
                $this->log->info($message);
            });
        } else {
            $baseDir = null;
        }

        switch ($data['action']) {
            case 'install':
                $plugin = $this->registerPlugin($plugin);
                $this->handleInstall($plugin, $model, $baseDir);
                break;

            case 'uninstall':
                $this->handleUninstall($plugin, $model, $baseDir);
                break;

            case 'update':
                $this->handleUpdate($plugin, $model, $baseDir);
                break;

            case 'enable':
                $plugin = $this->registerPlugin($plugin);
                $this->handleEnable($plugin, $model);
                break;

            case 'disable':
                $this->handleDisable($plugin, $model);
                break;
        }

        $routesCacheFile = $this->app->get('path.cache.routes');
        if (file_exists($routesCacheFile)) {
            unlink($routesCacheFile);
        }

        return $this->redirect->to('/admin/plugins');
    }

    protected function getAllPlugins(): Collection
    {
        $plugins = collect();
        /** @var AbstractPlugin $plugin */
        foreach ($this->app->tagged('plugin') as $plugin) {
            $plugins[] = $plugin;
        }
        foreach ($this->app->tagged('plugin.disabled') as $plugin) {
            $plugins[] = $plugin;
        }
        return $plugins
            ->keyBy(fn(AbstractPlugin $p) => $p->getPluginName())
            ->sortKeys();
    }

    protected function registerPlugin(AbstractPlugin $plugin): mixed
    {
        /** @var PluginServiceProvider $psp */
        $psp = $this->app->get(PluginServiceProvider::class);
        $psp->addPlugin($plugin->getPluginName(), $plugin->getPath(), true);
        return $this->app->get($plugin->getPluginName());
    }

    protected function handleInstall(AbstractPlugin $plugin, Plugin $model, ?string $baseDir): void
    {
        if ($baseDir) {
            $this->migration->run($baseDir, Direction::UP);
        }

        $model->save();

        $plugin->install();

        $this->log->info('Installed plugin "{name}"', ['name' => $plugin->getPluginName()]);
        $this->addNotification('plugin.install.success');
    }

    protected function handleUninstall(AbstractPlugin $plugin, Plugin $model, ?string $baseDir): void
    {
        $plugin->uninstall();

        if ($baseDir) {
            $this->migration->run($baseDir, Direction::DOWN);
        }

        $model->delete();

        $this->log->info('Uninstalled plugin "{name}"', ['name' => $plugin->getPluginName()]);
        $this->addNotification('plugin.uninstall.success');
    }

    protected function handleUpdate(AbstractPlugin $plugin, Plugin $model, ?string $baseDir): void
    {
        if ($baseDir) {
            $this->migration->run($baseDir, Direction::UP);
        }

        $plugin->update($model->version);

        $model->version = $plugin->getVersion();
        $model->save();

        $this->log->info('Updated plugin "{name}"', ['name' => $plugin->getPluginName()]);
        $this->addNotification('plugin.update.success');
    }

    protected function handleEnable(AbstractPlugin $plugin, Plugin $model): void
    {
        $model->enabled = true;
        $model->save();

        $plugin->enable();

        $this->log->info('Enabled plugin "{name}"', ['name' => $plugin->getPluginName()]);
        $this->addNotification('plugin.enable.success');
    }

    protected function handleDisable(AbstractPlugin $plugin, Plugin $model): void
    {
        $plugin->disable();

        $model->enabled = false;
        $model->save();

        $this->log->info('Disabled plugin "{name}"', ['name' => $plugin->getPluginName()]);
        $this->addNotification('plugin.disable.success');
    }
}
