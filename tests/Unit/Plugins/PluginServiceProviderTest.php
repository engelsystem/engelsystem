<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins;

use Engelsystem\Config\Config;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Models\Plugin as PluginModel;
use Engelsystem\Plugins\DisabledPlugin;
use Engelsystem\Plugins\Plugin;
use Engelsystem\Plugins\PluginServiceProvider;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\Plugins\Stub\InstallablePlugin\InstallablePlugin;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\EventHandler;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\Middleware;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\ServiceProvider;
use Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\TestPluginStateful;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Illuminate\Support\Str;
use Test\PluginStateful\TestClassInstance;

class PluginServiceProviderTest extends ServiceProviderTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::register
     */
    public function testRegisterNoTable(): void
    {
        // Ensure service provider register does not break if not migrated
        $this->database->getConnection()->getSchemaBuilder()->drop('plugins');

        /** @var PluginServiceProvider $serviceProvider */
        $serviceProvider = $this->app->make(PluginServiceProvider::class);
        $serviceProvider->register();

        $this->assertTrue(true);
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::boot
     */
    public function testBootNoTable(): void
    {
        // Ensure service provider boot does not break if not migrated
        $this->database->getConnection()->getSchemaBuilder()->drop('plugins');

        /** @var PluginServiceProvider $serviceProvider */
        $serviceProvider = $this->app->make(PluginServiceProvider::class);
        $serviceProvider->boot();

        $this->assertTrue(true);
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::register
     * @covers \Engelsystem\Plugins\PluginServiceProvider::addPlugin
     * @covers \Engelsystem\Plugins\PluginServiceProvider::getNamespace
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerNamespaces
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginInstance
     */
    public function testRegister(): void
    {
        $this->app->singleton(EventDispatcher::class);
        $this->app->singleton(EventHandler::class);
        $this->runRegister();

        $plugins = iterator_to_array($this->app->tagged('plugin'));
        $pluginsDisabled = iterator_to_array($this->app->tagged('plugin.disabled'));

        $pluginPaths = iterator_to_array($this->app->tagged('plugin.path'));
        $pluginPathsDisabled = iterator_to_array($this->app->tagged('plugin.path.disabled'));

        /** @var TestPluginStateful $plugin */
        $plugin = collect($plugins)->first(fn($p) => $p instanceof TestPluginStateful);
        $this->assertNotEmpty($plugin);
        $this->assertTrue(collect($pluginsDisabled)->contains(
            fn($p) => $p instanceof DisabledPlugin && $p->getPluginName() == 'NotEnabledPlugin'
        ));

        $this->assertContains(__DIR__ . '/Stub/TestPluginStateful/', $pluginPaths);
        $this->assertContains(__DIR__ . '/Stub/NotEnabledPlugin/', $pluginPathsDisabled);

        $this->assertEquals('TestPluginStateful', $plugin->getPluginName());
        $this->assertEquals(__DIR__ . '/Stub/TestPluginStateful/', $plugin->getPath());
        $this->assertEquals('Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\\', $plugin->getNamespace());
        $this->assertEquals(__DIR__ . '/Stub/TestPluginStateful/', $plugin->getNamespacePath());

        $this->assertTrue(class_exists(TestClassInstance::class));
        $this->assertFalse(class_exists('Test\PluginStateful\NotExisting'));

        $this->assertEquals($plugin, $this->app->get('TestPluginStateful'));
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::register
     * @covers \Engelsystem\Plugins\PluginServiceProvider::addPlugin
     * @covers \Engelsystem\Plugins\PluginServiceProvider::getNamespace
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginInstance
     */
    public function testRegisterDisabledOrBroken(): void
    {
        $invalidPlugins = ['EmptyDir', 'InvalidConfig', 'NoNamespace'];
        $this->app->instance('path.plugins', __DIR__ . '/Stub');

        /** @var PluginServiceProvider $provider */
        $provider = $this->app->get(PluginServiceProvider::class);
        $provider->register();

        $plugins = iterator_to_array($this->app->tagged('plugin'));
        $pluginsDisabled = iterator_to_array($this->app->tagged('plugin.disabled'));

        $pluginPaths = iterator_to_array($this->app->tagged('plugin.path'));
        $pluginPathsDisabled = iterator_to_array($this->app->tagged('plugin.path.disabled'));

        $this->assertEmpty($plugins);
        $this->assertTrue(collect($pluginsDisabled)->contains(
            fn($p) => $p instanceof DisabledPlugin && $p->getPluginName() == 'TestPluginStateful'
        ));
        $this->assertTrue(collect($pluginsDisabled)->contains(
            fn($p) => $p instanceof DisabledPlugin && $p->getPluginName() == 'NotEnabledPlugin'
        ));
        $this->assertFalse(collect($pluginsDisabled)->contains(function (mixed $plugin) use ($invalidPlugins): bool {
            return
                !$plugin instanceof Plugin // No "empty" entries
                || in_array( // Ignore invalid plugins
                    $plugin->getPluginName(),
                    $invalidPlugins
                );
        }));

        $this->assertEmpty($pluginPaths);
        $this->assertContains(__DIR__ . '/Stub/TestPluginStateful/', $pluginPathsDisabled);
        $this->assertContains(__DIR__ . '/Stub/NotEnabledPlugin/', $pluginPathsDisabled);
        $this->assertFalse(collect($pluginPaths)->contains(fn($path) => Str::contains($path, $invalidPlugins)));
        $this->assertInstanceOf(DisabledPlugin::class, $this->app->get(TestPluginStateful::class));
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::boot
     */
    public function testBoot(): void
    {
        $this->app->singleton(ServiceProvider::class);

        $plugin = new TestPluginStateful([
            'name' => 'test/plugin',
            'plugin_name' => 'TestPlugin',
            'path' => '/tmp',
            'namespace' => 'Engelsystem\Test\Unit\Plugins\Stub',
            'namespace_path' => __DIR__ . '/Stub',
            'extra' => ['providers' => [ServiceProvider::class]],
        ]);
        $this->app->instance('p', $plugin);
        $this->app->tag('p', 'plugin');

        /** @var PluginServiceProvider $provider */
        $provider = $this->app->make(PluginServiceProvider::class);

        $provider->boot();

        /** @var ServiceProvider $serviceProvider */
        $serviceProvider = $this->app->get(ServiceProvider::class);

        $this->assertTrue($plugin->booted);
        $this->assertTrue($serviceProvider->booted);
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginInstance
     */
    public function testRegisterPluginInstanceRebind(): void
    {
        $provider = $this->runRegister();

        // Assert init state
        $this->assertInstanceOf(DisabledPlugin::class, $this->app->get('InstallablePlugin'));

        // Save as enabled
        $provider->addPlugin(
            'InstallablePlugin',
            __DIR__ . '/Stub/InstallablePlugin/',
            true
        );

        $this->assertInstanceOf(InstallablePlugin::class, $this->app->get('InstallablePlugin'));
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginProviders
     */
    public function testRegisterPluginProviders(): void
    {
        $this->runRegister();

        /** @var Config $config */
        $config = $this->app->get(Config::class);
        /** @var ServiceProvider $serviceProvider */
        $serviceProvider = $this->app->get(ServiceProvider::class);
        $this->assertTrue($serviceProvider->registered);
        $this->assertContains(ServiceProvider::class, $config->get('providers', []));
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginMiddleware
     */
    public function testRegisterPluginMiddleware(): void
    {
        $this->runRegister();

        $middlewares = iterator_to_array($this->app->tagged('plugin.middleware'));
        $this->assertTrue(collect($middlewares)->contains(fn($p) => $p instanceof Middleware));
    }

    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginEventHandlers
     */
    public function testRegisterPluginEventHandlers(): void
    {
        $this->app->singleton(EventDispatcher::class);
        $this->app->singleton(EventHandler::class);
        $this->runRegister();

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->app->get(EventDispatcher::class);
        $dispatcher->dispatch('test_event');
        /** @var EventHandler $handler */
        $handler = $this->app->get(EventHandler::class);
        $this->assertTrue($handler->handled);
    }


    /**
     * @covers \Engelsystem\Plugins\PluginServiceProvider::registerPluginConfigOptions
     */
    public function testRegisterPluginConfigOptions(): void
    {
        $this->runRegister();

        /** @var Config $config */
        $config = $this->app->get(Config::class);
        $options = $config->get('config_options', []);
        $this->assertArrayHasKey('stateful', $options);
    }

    protected function runRegister(): PluginServiceProvider
    {
        $this->app->instance('path.plugins', __DIR__ . '/Stub');

        PluginModel::factory()->create([
            'name' => 'TestPluginStateful',
            'enabled' => true,
            'version' => '0.1.3-test',
        ]);

        /** @var PluginServiceProvider $provider */
        $provider = $this->app->get(PluginServiceProvider::class);
        $provider->register();

        return $provider;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        $this->app->instance(Config::class, new Config());
        $this->app->alias(Config::class, 'config');
    }
}
