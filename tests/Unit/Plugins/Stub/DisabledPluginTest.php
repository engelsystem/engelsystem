<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub;

use Engelsystem\Plugins\DisabledPlugin;
use PHPUnit\Framework\TestCase;

class DisabledPluginTest extends TestCase
{
    /**
     * @covers \Engelsystem\Plugins\DisabledPlugin::__construct
     */
    public function testConstructor(): void
    {
        $plugin = new DisabledPlugin([
            'name' => 'test/plugin',
            'plugin_name' => 'TestPlugin',
            'path' => '/tmp',
            'namespace' => 'Test\\Plugin',
            'namespace_path' => '/tmp/src',
            'providers' => ['Test\\Plugin\\Provider'],
            'middleware' => ['Test\\Plugin\\Middleware'],
            'event_handlers' => ['foo' => 'Test\\Plugin\\Event'],
            'config_options' => ['test' => []],
            'routes' => ['/test' => 'Test\\Plugin\\Controller'],
        ]);

        $this->assertEquals([], $plugin->getProviders());
        $this->assertEquals([], $plugin->getMiddleware());
        $this->assertEquals([], $plugin->getEventHandlers());
        $this->assertEquals([], $plugin->getConfigOptions());
        $this->assertEquals([], $plugin->getRoutes());
    }
}
