<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins;

use Engelsystem\Test\Unit\Plugins\Stub\PluginImplementation;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;

use function FastRoute\simpleDispatcher;

class PluginTest extends TestCase
{
    protected array $minData = [
        'name' => 'test/plugin',
        'plugin_name' => 'TestPlugin',
        'path' => '/tmp',
        'namespace' => 'Test\\Plugin',
        'namespace_path' => '/tmp/src',
    ];

    /**
     * @covers \Engelsystem\Plugins\Plugin::__construct
     */
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

    /**
     * @covers \Engelsystem\Plugins\Plugin::loadRoutes
     */
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

    /**
     * @covers \Engelsystem\Plugins\Plugin::boot
     * @covers \Engelsystem\Plugins\Plugin::install
     * @covers \Engelsystem\Plugins\Plugin::uninstall
     * @covers \Engelsystem\Plugins\Plugin::update
     * @covers \Engelsystem\Plugins\Plugin::enable
     * @covers \Engelsystem\Plugins\Plugin::disable
     */
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

    /**
     * @covers \Engelsystem\Plugins\Plugin::__construct
     * @covers \Engelsystem\Plugins\Plugin::getName
     * @covers \Engelsystem\Plugins\Plugin::getPluginName
     * @covers \Engelsystem\Plugins\Plugin::getPath
     * @covers \Engelsystem\Plugins\Plugin::getNamespace
     * @covers \Engelsystem\Plugins\Plugin::getNamespacePath
     * @covers \Engelsystem\Plugins\Plugin::getDescription
     * @covers \Engelsystem\Plugins\Plugin::getVersion
     * @covers \Engelsystem\Plugins\Plugin::getLicense
     * @covers \Engelsystem\Plugins\Plugin::getHomepage
     * @covers \Engelsystem\Plugins\Plugin::getAuthors
     * @covers \Engelsystem\Plugins\Plugin::getExtra
     * @covers \Engelsystem\Plugins\Plugin::getProviders
     * @covers \Engelsystem\Plugins\Plugin::getMiddleware
     * @covers \Engelsystem\Plugins\Plugin::getEventHandlers
     * @covers \Engelsystem\Plugins\Plugin::getConfigOptions
     * @covers \Engelsystem\Plugins\Plugin::getRoutes
     */
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
