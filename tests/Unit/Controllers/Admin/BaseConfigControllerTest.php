<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Test\Unit\Controllers\Admin\Stub\BaseConfigControllerImplementation;
use Engelsystem\Test\Unit\Controllers\ControllerTest;

class BaseConfigControllerTest extends ControllerTest
{
    protected array $options = [
        'test' => [
            'config' => [
                'something' => [
                    'type' => 'text',
                ],
                'some_config_from_env' => [
                    'type' => 'text',
                    'required' => true,
                    'write_back' => true,
                ],
                'hidden_stuff' => [
                    'type' => 'text',
                    'hidden' => true,
                ],
                'drops' => [
                    'type' => 'select',
                    'data' => [
                        'first',
                        'second',
                    ],
                ],
            ],
        ],
        'foo' => [
            'order' => 20,
            'config' => [],
        ],
        'bar' => [
            'order' => 10,
            'config' => [],
        ],
        'lorem' => [
            'config' => [],
            'title' => 'some.title',
            'url' => '/test',
        ],
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\BaseConfigController::__construct
     */
    public function testConstruct(): void
    {
        $controller = new BaseConfigControllerImplementation();
        // Ensure order
        $this->assertEquals(['test', 'bar', 'foo', 'lorem'], array_keys($controller->getOptions()));
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\BaseConfigController::__construct
     */
    public function testDefaultPermissions(): void
    {
        $controller = new BaseConfigControllerImplementation();
        $this->assertEquals(['config.edit'], $controller->getPermissions());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\BaseConfigController::getPageData
     */
    public function testGetPageData(): void
    {
        $controller = new BaseConfigControllerImplementation();
        $controller->callParseOptions();

        $pageData = $controller->callGetPageData('test');
        $this->assertArrayHasKey('page', $pageData);
        $this->assertEquals('test', $pageData['page']);

        $this->assertArrayHasKey('title', $pageData);
        $this->assertEquals('config.test', $pageData['title']);

        $this->assertArrayHasKey('config', $pageData);
        $this->assertArrayHasKey('something', $pageData['config']);

        $this->assertArrayHasKey('options', $pageData);
        $this->assertArrayHasKey('test', $pageData['options']);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\BaseConfigController::parseOptions
     */
    public function testParseOptions(): void
    {
        $controller = new BaseConfigControllerImplementation();
        $controller->callParseOptions();

        $pageData = $controller->callGetPageData('test');
        $options = $pageData['options'];
        $this->assertArrayHasKey('test', $options);
        $this->assertArrayHasKey('bar', $options);
        $this->assertArrayHasKey('foo', $options);
        $this->assertArrayHasKey('lorem', $options);

        $value = $options['test'];
        $this->assertEquals('/admin/config/test', $value['url']);
        $this->assertTrue($value['url_added']);
        $this->assertEquals('config.test', $value['title']);

        $this->assertIsCallable($options['foo']['validation']);

        $config = $value['config'];
        $this->assertArrayHasKey('something', $config);
        $this->assertArrayHasKey('some_config_from_env', $config);
        $this->assertArrayHasKey('drops', $config);
        $this->assertArrayNotHasKey('hidden_stuff', $config);

        $this->assertEquals('config.something', $config['something']['name']);
        $this->assertTrue($config['some_config_from_env']['required_icon']);

        $this->assertEquals(
            ['first' => 'config.drops.select.first', 'second' => 'config.drops.select.second'],
            $config['drops']['data']
        );

        $this->assertTrue($config['some_config_from_env']['in_env']);
        $this->assertFalse($config['some_config_from_env']['writable']);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->config->set('config_options', $this->options);
        $this->config->set('env_config', ['SOME_CONFIG_FROM_ENV' => 'I am the env!']);

        $url = $this->getMockForAbstractClass(UrlGeneratorInterface::class);
        $url->expects($this->any())
            ->method('to')
            ->willReturnCallback(function (string $path, array $parameters = []) {
                return $path . ($parameters ? '?' . http_build_query($parameters) : '');
            });
        $this->app->instance('http.urlGenerator', $url);
    }
}
