<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins;

use Engelsystem\Plugins\Plugin;
use Engelsystem\Test\Unit\Plugins\Stub\PluginImplementation;
use FastRoute\RouteCollector;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function FastRoute\simpleDispatcher;

#[CoversMethod(Plugin::class, '__construct')]
#[CoversMethod(Plugin::class, 'boot')]
#[CoversMethod(Plugin::class, 'install')]
#[CoversMethod(Plugin::class, 'uninstall')]
#[CoversMethod(Plugin::class, 'update')]
#[CoversMethod(Plugin::class, 'enable')]
#[CoversMethod(Plugin::class, 'disable')]
#[CoversMethod(Plugin::class, 'loadRoutes')]
#[CoversMethod(Plugin::class, 'getName')]
#[CoversMethod(Plugin::class, 'getPluginName')]
#[CoversMethod(Plugin::class, 'getPath')]
#[CoversMethod(Plugin::class, 'getNamespace')]
#[CoversMethod(Plugin::class, 'getNamespacePath')]
#[CoversMethod(Plugin::class, 'getDescription')]
#[CoversMethod(Plugin::class, 'getVersion')]
#[CoversMethod(Plugin::class, 'getLicense')]
#[CoversMethod(Plugin::class, 'getHomepage')]
#[CoversMethod(Plugin::class, 'getAuthors')]
#[CoversMethod(Plugin::class, 'getExtra')]
#[CoversMethod(Plugin::class, 'getProviders')]
#[CoversMethod(Plugin::class, 'getMiddleware')]
#[CoversMethod(Plugin::class, 'getEventHandlers')]
#[CoversMethod(Plugin::class, 'getConfigOptions')]
#[CoversMethod(Plugin::class, 'getRoutes')]
class PluginTest extends TestCase
{
    protected array $minData = [
        'name' => 'test/plugin',
        'plugin_name' => 'TestPlugin',
        'path' => '/tmp',
        'namespace' => 'Test\\Plugin',
        'namespace_path' => '/tmp/src',
    ];

    public function testConstructorMinimal(): void
    {
        $plugin = new PluginImplementation($this->minData);

        $this->assertEquals('test/plugin', $plugin->getName());
        $this->assertEquals('TestPlugin', $plugin->getPluginName());
        $this->assertEquals('/tmp', $plugin->getPath());
        $this->assertEquals('Test\\Plugin', $plugin->getNamespace());
        $this->assertEquals('/tmp/src', $plugin->getNamespacePath());
        $this->assertNull($plugin->getDescription());
        $this->assertEquals('0.0.0', $plugin->getVersion());
        $this->assertEquals('proprietary', $plugin->getLicense());
        $this->assertNull($plugin->getHomepage());
        $this->assertEquals([], $plugin->getAuthors());
        $this->assertEquals([], $plugin->getExtra());
        $this->assertEquals([], $plugin->getProviders());
        $this->assertEquals([], $plugin->getMiddleware());
        $this->assertEquals([], $plugin->getEventHandlers());
        $this->assertEquals([], $plugin->getConfigOptions());
        $this->assertEquals([], $plugin->getRoutes());
    }

    public function testLoadRoutes(): void
    {
        $plugin = new PluginImplementation([
            ...$this->minData,
            'extra' => [
                'routes' => [
                    '/test' => 'Test\\Plugin\\Controller',
                    '/demo' => ['Test\\Plugin\\Controller@post', 'POST'],
                ],
            ],
        ]);

        simpleDispatcher(function (RouteCollector $route) use ($plugin): void {
            $plugin->loadRoutes($route);
            $this->assertEquals([[
                'GET' => ['/test' => 'Test\\Plugin\\Controller'],
                'POST' => ['/demo' => 'Test\\Plugin\\Controller@post'],
            ], []], $route->getData());
        });
    }

    public function testMethods(): void
    {
        $pluginDefault = new PluginImplementation($this->minData);
        $plugin = new PluginImplementation($this->minData);
        $plugin->boot();
        $plugin->install();
        $plugin->uninstall();
        $plugin->update('0.0.0');
        $plugin->enable();
        $plugin->disable();

        $this->assertEquals($pluginDefault, $plugin);
    }

    public function testConstructorAndGetters(): void
    {
        $plugin = new PluginImplementation([
            ...$this->minData,
            'description' => 'Testing stuff',
            'version' => '42.23.0-alpha1',
            'license' => ['proprietary', 'GPL-3.0-or-later'],
            'homepage' => 'https://example.com',
            'authors' => ['name' => 'Test Tester'],
            'extra' => [
                'test' => true,
                'providers' => ['Test\\Plugin\\Provider'],
                'middleware' => ['Test\\Plugin\\Middleware'],
                'event_handlers' => ['foo' => 'Test\\Plugin\\Event'],
                'config_options' => ['test' => []],
                'routes' => ['/test' => 'Test\\Plugin\\Controller'],
            ],
        ]);

        $this->assertEquals('test/plugin', $plugin->getName());
        $this->assertEquals('TestPlugin', $plugin->getPluginName());
        $this->assertEquals('/tmp', $plugin->getPath());
        $this->assertEquals('Test\\Plugin', $plugin->getNamespace());
        $this->assertEquals('/tmp/src', $plugin->getNamespacePath());
        $this->assertEquals('Testing stuff', $plugin->getDescription());
        $this->assertEquals('42.23.0-alpha1', $plugin->getVersion());
        $this->assertEquals('proprietary, GPL-3.0-or-later', $plugin->getLicense());
        $this->assertEquals('https://example.com', $plugin->getHomepage());
        $this->assertEquals(['name' => 'Test Tester'], $plugin->getAuthors());
        $this->assertEquals([
            'test' => true,
            'providers' => ['Test\\Plugin\\Provider'],
            'middleware' => ['Test\\Plugin\\Middleware'],
            'event_handlers' => ['foo' => 'Test\\Plugin\\Event'],
            'config_options' => ['test' => []],
            'routes' => ['/test' => 'Test\\Plugin\\Controller'],
        ], $plugin->getExtra());
        $this->assertEquals(['Test\\Plugin\\Provider'], $plugin->getProviders());
        $this->assertEquals(['Test\\Plugin\\Middleware'], $plugin->getMiddleware());
        $this->assertEquals(['foo' => 'Test\\Plugin\\Event'], $plugin->getEventHandlers());
        $this->assertEquals(['test' => []], $plugin->getConfigOptions());
        $this->assertEquals(['/test' => 'Test\\Plugin\\Controller'], $plugin->getRoutes());
    }
}
