<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\PluginsController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Plugin as PluginModel;
use Engelsystem\Plugins\Plugin;
use Engelsystem\Plugins\PluginServiceProvider;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\TestPluginStateful;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PluginsControllerTest extends ControllerTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::__construct
     * @covers \Engelsystem\Controllers\Admin\PluginsController::list
     * @covers \Engelsystem\Controllers\Admin\PluginsController::getAllPlugins
     */
    public function testList(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data): Response {
                $this->assertEquals('admin/plugin.twig', $view);
                $this->assertArrayHasKey('plugins', $data);
                $this->assertArrayHasKey('installedPlugins', $data);

                /** @var Collection|Plugin[] $plugins */
                $plugins = collect($data['plugins']);
                /** @var EloquentCollection|PluginModel[] $installedPlugins */
                $installedPlugins = $data['installedPlugins'];

                $this->assertTrue($plugins->has('CurrentlyEnabledPlugin'));
                $this->assertTrue($plugins->has('InstallablePlugin'));
                $this->assertTrue($plugins->has('NotEnabledPlugin'));
                $this->assertTrue($plugins->has('TestPluginStateful'));

                $this->assertTrue($installedPlugins->has('CurrentlyEnabledPlugin'));
                $this->assertTrue($installedPlugins->has('TestPluginStateful'));
                $this->assertTrue($installedPlugins->has('NotEnabledPlugin'));
                $this->assertTrue($installedPlugins->has('DeletedPlugin'));

                return $this->response;
            });

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $return = $controller->list();

        $this->assertEquals($this->response, $return);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     */
    public function testUpdateStateNotFound(): void
    {
        $request = new Request([], [], ['plugin' => 'PluginThatDoesNotExist']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);

        $this->expectException(HttpNotFound::class);
        $controller->updateState($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     */
    public function testUpdateStateInvalidRequest(): void
    {
        $request = new Request([], [], ['plugin' => 'TestPluginStateful']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->updateState($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleInstall
     * @covers \Engelsystem\Controllers\Admin\PluginsController::registerPlugin
     */
    public function testUpdateStateInstall(): void
    {
        $request = new Request([], ['action' => 'install'], ['plugin' => 'TestPluginStateful']);
        PluginModel::whereName('TestPluginStateful')->delete();

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertTrue($this->log->hasInfo('Migrating 2026_01_04_000000_first_migration (up)'));
        $this->assertTrue($this->log->hasInfo('Migrating 2026_01_04_000001_second_migration (up)'));
        $this->assertTrue($this->log->hasInfoThatContains('Installed plugin'));
        $this->assertHasNotification('plugin.install.success');

        /** @var PluginModel $plugin */
        $plugin = PluginModel::whereName('TestPluginStateful')->first();
        $this->assertNotEmpty($plugin);
        $this->assertEquals('TestPluginStateful', $plugin->name);
        $this->assertTrue($plugin->enabled);
        $this->assertEquals('1.2.3', $plugin->version);

        /** @var TestPluginStateful $pluginInstance */
        $pluginInstance = $this->app->get(TestPluginStateful::class);
        $this->assertInstanceOf(TestPluginStateful::class, $pluginInstance);
        $this->assertTrue($pluginInstance->installed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleUninstall
     */
    public function testUpdateStateUninstall(): void
    {
        $this->setMigrated('2026_01_04_000000_first_migration');
        $request = new Request([], ['action' => 'uninstall'], ['plugin' => 'TestPluginStateful']);
        /** @var TestPluginStateful $pluginInstance */
        $pluginInstance = $this->app->get(TestPluginStateful::class);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertTrue($this->log->hasInfo('Migrating 2026_01_04_000000_first_migration (down)'));
        $this->assertFalse($this->log->hasInfo('Migrating 2026_01_04_000001_second_migration (down)'));
        $this->assertTrue($this->log->hasInfoThatContains('Uninstalled plugin'));
        $this->assertHasNotification('plugin.uninstall.success');

        $plugin = PluginModel::whereName('TestPluginStateful')->first();
        $this->assertNull($plugin);
        $this->assertTrue($pluginInstance->uninstalled);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleUpdate
     */
    public function testUpdateStateUpdate(): void
    {
        $this->setMigrated('2026_01_04_000000_first_migration');
        $request = new Request([], ['action' => 'update'], ['plugin' => 'TestPluginStateful']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertFalse($this->log->hasInfo('Migrating 2026_01_04_000000_first_migration (up)'));
        $this->assertTrue($this->log->hasInfo('Migrating 2026_01_04_000001_second_migration (up)'));
        $this->assertTrue($this->log->hasInfoThatContains('Updated plugin'));
        $this->assertHasNotification('plugin.update.success');

        /** @var PluginModel $plugin */
        $plugin = PluginModel::whereName('TestPluginStateful')->first();
        $this->assertEquals('1.2.3', $plugin->version);

        /** @var TestPluginStateful $pluginInstance */
        $pluginInstance = $this->app->get(TestPluginStateful::class);
        $this->assertTrue($pluginInstance->updated);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleUpdate
     */
    public function testUpdateStateUpdateNoMigrations(): void
    {
        $request = new Request([], ['action' => 'update'], ['plugin' => 'CurrentlyEnabledPlugin']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertFalse($this->log->hasInfoThatContains('Migrating'));
        $this->assertTrue($this->log->hasInfoThatContains('Updated plugin'));
        $this->assertHasNotification('plugin.update.success');
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleEnable
     * @covers \Engelsystem\Controllers\Admin\PluginsController::registerPlugin
     */
    public function testUpdateStateEnable(): void
    {
        $request = new Request([], ['action' => 'enable'], ['plugin' => 'TestPluginStateful']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertTrue($this->log->hasInfoThatContains('Enabled plugin'));
        $this->assertHasNotification('plugin.enable.success');

        /** @var PluginModel $plugin */
        $plugin = PluginModel::whereName('TestPluginStateful')->first();
        $this->assertTrue($plugin->enabled);

        /** @var TestPluginStateful $pluginInstance */
        $pluginInstance = $this->app->get(TestPluginStateful::class);
        $this->assertInstanceOf(TestPluginStateful::class, $pluginInstance);
        $this->assertTrue($pluginInstance->enabled);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     * @covers \Engelsystem\Controllers\Admin\PluginsController::handleDisable
     */
    public function testUpdateStateDisable(): void
    {
        $request = new Request([], ['action' => 'disable'], ['plugin' => 'TestPluginStateful']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertTrue($this->log->hasInfoThatContains('Disabled plugin'));
        $this->assertHasNotification('plugin.disable.success');

        /** @var PluginModel $plugin */
        $plugin = PluginModel::whereName('TestPluginStateful')->first();
        $this->assertFalse($plugin->enabled);

        /** @var TestPluginStateful $pluginInstance */
        $pluginInstance = $this->app->get(TestPluginStateful::class);
        $this->assertInstanceOf(TestPluginStateful::class, $pluginInstance);
        $this->assertTrue($pluginInstance->disabled);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\PluginsController::updateState
     */
    public function testUpdateStateClearCache(): void
    {
        $routesCacheFile = tempnam(sys_get_temp_dir(), 'RoutesCache.php');
        $this->app->instance('path.cache.routes', $routesCacheFile);
        touch($routesCacheFile);

        $request = new Request([], ['action' => 'install'], ['plugin' => 'TestPluginStateful']);

        /** @var PluginsController $controller */
        $controller = $this->app->make(PluginsController::class);
        $controller->setValidator(new Validator());
        $controller->updateState($request);

        $this->assertFalse(file_exists($routesCacheFile));
    }

    protected function setMigrated(string $string): void
    {
        /** @var Connection $db */
        $db = $this->app->get(Connection::class);
        $db->table('migrations')->insert(['migration' => $string]);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->app->instance('path.plugins', __DIR__ . '/../../Plugins/Stub');

        PluginModel::factory()->create([
            'name' => 'CurrentlyEnabledPlugin',
            'enabled' => true,
            'version' => '0.0.1',
        ]);
        PluginModel::factory()->create([
            'name' => 'TestPluginStateful',
            'enabled' => true,
            'version' => '0.1.3-test',
        ]);
        PluginModel::factory()->create([
            'name' => 'NotEnabledPlugin',
            'enabled' => false,
            'version' => '0.0.1',
        ]);
        PluginModel::factory()->create([
            'name' => 'DeletedPlugin',
            'enabled' => true,
            'version' => '42.23.0-test',
        ]);

        /** @var PluginServiceProvider $serviceProvider */
        $serviceProvider = $this->app->get(PluginServiceProvider::class);
        $serviceProvider->register();
        $serviceProvider->boot();
    }
}
