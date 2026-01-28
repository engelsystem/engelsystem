<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\ConfigController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\Controllers\Stub\AccessibleAuthenticator;
use InvalidArgumentException;

class ConfigControllerTest extends ControllerTest
{
    protected AccessibleAuthenticator $auth;

    protected array $options = [
        'test' => [
            'config' => [
                'foo' => [
                    'type' => 'text',
                    'required' => 'true',
                ],
                'bar' => [
                    'type' => 'datetime-local',
                    'name' => 'some.bar',
                ],
                'baz' => [
                    'type' => 'text',
                    'default' => 'default value',
                    'permission' => 'another_test_permission',
                ],
                'something_to_hide' => [
                    'type' => 'boolean',
                    'hidden' => true,
                ],
                'some_config_from_env' => [
                    'type' => 'text',
                ],
                'selectable_option' => [
                    'type' => 'select',
                    'data' => [
                        'first',
                        'second' => 'second translated',
                        'third',
                    ],
                ],
                'multiselectable_option' => [
                    'type' => 'select_multi',
                    'data' => [
                        'first',
                        'second',
                    ],
                ],
                'password' => [
                    'type' => 'password',
                ],
                'password_validation' => [
                    'type' => 'password',
                    'validation' => [
                        'length:16',
                    ],
                ],
                'numeric' => [
                    'type' => 'number',
                    'validation' => [
                        'min:5',
                    ],
                ],
                'url' => [
                    'type' => 'url',
                ],
                'to_be_written_to_file' => [
                    'type' => 'boolean',
                    'write_back' => true,
                    'default' => false,
                ],
                'element.key' => [
                    'type' => 'date',
                ],
            ],
            'permission' => 'some_test_permission',
        ],
        'invalid' => [
            'config' => [
                'broken' => [
                    'type' => 'not_existing_input_type',
                ],
            ],
        ],
        'event' => [
            'config' => [
                'name' => [
                    'type' => 'string',
                ],
                'welcome_msg' => [
                    'type' => 'text',
                    'rows' => 5,
                ],
                'buildup_start' => [
                    'type' => 'datetime-local',
                ],
                'event_start' => [
                    'type' => 'datetime-local',
                ],
                'event_end' => [
                    'type' => 'datetime-local',
                ],
                'teardown_end' => [
                    'type' => 'datetime-local',
                ],
                'enable_day_of_event' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'needed_field' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ],
    ];

    protected array $validEventBody = [
        'name' => '',
        'welcome_msg' => '',
        'buildup_start' => '',
        'event_start' => '',
        'event_end' => '',
        'teardown_end' => '',
        'needed_field' => 'some field value',
    ];

    protected array $validTestBody = [
        'foo' => 'some text',
        'bar' => '2042-01-01T00:00',
        'baz' => 'New value',
        'some_config_from_env' => 'Lorem Ipsum',
        'selectable_option' => 'third',
        'multiselectable_option' => ['first', 'second'],
        'password' => 'FooBarBaz42!',
        'password_validation' => '0123456789aBcDeF',
        'numeric' => 1337,
        'url' => 'https://example.com/test',
        'to_be_written_to_file' => '1',
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::index
     */
    public function testIndex(): void
    {
        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $this->setExpects($this->response, 'redirectTo', ['http://localhost/admin/config/test'], $this->response);

        $response = $controller->index();
        $this->assertEquals($this->response, $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::__construct
     * @covers \Engelsystem\Controllers\Admin\ConfigController::edit
     * @covers \Engelsystem\Controllers\Admin\ConfigController::activePage
     * @covers \Engelsystem\Controllers\Admin\ConfigController::parseOptions
     */
    public function testEdit(): void
    {
        $this->request->attributes->set('page', 'test');
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('admin/config/index', $view);

                $this->assertEquals('test', $data['page']);

                $this->assertNotEmpty($data['title']);
                $this->assertNotEmpty($data['config']);
                $this->assertNotEmpty($data['options']);

                $this->assertArrayHasKey('foo', $data['config']);
                $this->assertArrayHasKey('bar', $data['config']);
                $this->assertArrayHasKey('some_config_from_env', $data['config']);

                $this->assertArrayHasKey('name', $data['config']['foo']);
                $this->assertArrayHasKey('type', $data['config']['foo']);
                $this->assertArrayHasKey('required', $data['config']['foo']);
                $this->assertArrayHasKey('required_icon', $data['config']['foo']);
                $this->assertEquals('config.foo', $data['config']['foo']['name']);

                $this->assertArrayHasKey('name', $data['config']['bar']);
                $this->assertEquals('some.bar', $data['config']['bar']['name']);

                $this->assertArrayHasKey('in_env', $data['config']['some_config_from_env']);
                $this->assertTrue($data['config']['some_config_from_env']['in_env']);

                $this->assertArrayHasKey('in_env', $data['config']['some_config_from_env']);
                $this->assertEquals(
                    [
                        'first' => 'config.selectable_option.select.first',
                        'second' => 'second translated',
                        'third' => 'config.selectable_option.select.third',
                    ],
                    $data['config']['selectable_option']['data'],
                );
                $this->assertEquals(
                    [
                        'first' => 'config.multiselectable_option.select.first',
                        'second' => 'config.multiselectable_option.select.second',
                    ],
                    $data['config']['multiselectable_option']['data'],
                );

                $this->assertArrayHasKey('test', $data['options']);
                $this->assertArrayHasKey('url', $data['options']['test']);
                $this->assertArrayHasKey('title', $data['options']['test']);
                $this->assertEquals('config.test', $data['options']['test']['title']);
                $this->assertEquals('http://localhost/admin/config/test', $data['options']['test']['url']);

                $this->assertArrayNotHasKey('something_to_hide', $data['config']);

                return $this->response;
            });

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $response = $controller->edit($this->request);
        $this->assertEquals($this->response, $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::isFileWritable
     * @covers \Engelsystem\Controllers\Admin\ConfigController::parseOptions
     */
    public function testEditNotWritable(): void
    {
        $notWritableDir = __DIR__ . '/Stub/SubDir/Not/Existing';
        $this->app->instance('path.config', $notWritableDir);

        $this->request->attributes->set('page', 'test');
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertArrayHasKey('to_be_written_to_file', $data['config']);
                $this->assertArrayHasKey('writable', $data['config']['to_be_written_to_file']);
                $this->assertFalse($data['config']['to_be_written_to_file']['writable']);
                return $this->response;
            });

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $response = $controller->edit($this->request);
        $this->assertEquals($this->response, $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::activePage
     */
    public function testActivePageNotFound(): void
    {
        $this->request->attributes->set('page', '404');

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $this->expectException(HttpNotFound::class);
        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::activePage
     */
    public function testActivePageNotAllowed(): void
    {
        $this->auth->setPermissions(['none']);
        $this->request->attributes->set('page', 'test');

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $this->expectException(HttpForbidden::class);
        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveTestValidateInvalidInputType(): void
    {
        $this->request->attributes->set('page', 'invalid');

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(InvalidArgumentException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @covers \Engelsystem\Controllers\Admin\ConfigController::filterShownSettings
     * @covers \Engelsystem\Controllers\Admin\ConfigController::parseOptions
     */
    public function testSaveTestValidateSuccessful(): void
    {
        $this->request->attributes->set('page', 'test');
        $this->request = $this->request->withParsedBody($this->validTestBody);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);
        $this->assertEquals('some text', EventConfig::whereName('foo')->first()->value);
        $this->assertEquals(
            '2042-01-01T00:00:00.000000Z',
            EventConfig::whereName('bar')->first()->value
        );
        $this->assertNull(EventConfig::whereName('some_config_from_env')->first());
        $this->assertNull(EventConfig::whereName('baz')->first());
        $this->assertEquals('third', EventConfig::whereName('selectable_option')->first()->value);
        $this->assertEquals(['first', 'second'], EventConfig::whereName('multiselectable_option')->first()->value);
        $this->assertEquals('FooBarBaz42!', EventConfig::whereName('password')->first()->value);
        $this->assertEquals('0123456789aBcDeF', EventConfig::whereName('password_validation')->first()->value);
        $this->assertEquals(1337, EventConfig::whereName('numeric')->first()->value);
        $this->assertEquals('https://example.com/test', EventConfig::whereName('url')->first()->value);

        // Save with additional permission
        $this->auth->setPermissions(['some_test_permission', 'another_test_permission']);
        $controller->save($this->request);
        $baz = EventConfig::whereName('baz')->first();
        $this->assertNotNull($baz);
        $this->assertEquals('New value', $baz->value);
    }

    public function validationErrorsProvider(): array
    {
        return [
            [['selectable_option' => 'not selectable']],
            [['multiselectable_option' => 'not array']],
            [['multiselectable_option' => ['not_in_values']]],
            [['password' => 'short']],
            [['password_validation' => 'shorter']],
            [['url' => 'not an URL']],
            [['numeric' => 3]],
        ];
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @dataProvider validationErrorsProvider
     */
    public function testSaveValidationError(array $errorData): void
    {
        $this->request->attributes->set('page', 'test');
        $data = array_merge($this->validTestBody, $errorData);
        $this->request = $this->request->withParsedBody($data);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveTestPasswordIgnorePlaceholder(): void
    {
        $passwordPlaceholder = '**********';
        $this->request->attributes->set('page', 'test');
        $data = $this->validTestBody;
        $data['password'] = $passwordPlaceholder;
        $data['password_validation'] = $passwordPlaceholder;
        $this->request = $this->request->withParsedBody($data);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);
        $this->assertNull(EventConfig::whereName('password')->first());
        $this->assertNull(EventConfig::whereName('password_validation')->first());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveTestValidateRequiredError(): void
    {
        $this->request->attributes->set('page', 'test');

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveCreateInvalid(): void
    {
        $this->request = $this->request->withParsedBody(array_merge($this->validEventBody, [
            'buildup_start' => 'invalid date',
        ]));

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validateEvent
     */
    public function testSaveValidateEventDates(): void
    {
        $this->request = $this->request->withParsedBody(array_merge($this->validEventBody, [
            'event_end' => '2042-01-01T00:00',
            'teardown_end' => '2000-01-01T00:00',
        ]));

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveEmpty(): void
    {
        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validateEvent
     */
    public function testSaveEmptyValues(): void
    {
        $this->request = $this->request->withParsedBody($this->validEventBody);
        $this->app->instance(Request::class, $this->request);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/')
            ->willReturn($this->response);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertEmpty(EventConfig::where('name', '!=', 'needed_field')->get());

        $this->assertTrue($this->log->hasInfoThatContains('Updated'));
        $this->assertHasNotification('config.edit.success');
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validateEvent
     */
    public function testSave(): void
    {
        $body = array_merge($this->validEventBody, [
            'name' => 'TestEvent',
            'welcome_msg' => 'Welcome!',
            'buildup_start' => '2001-01-01T10:42',
            'event_start' => '2010-01-01T00:00',
            'event_end' => '2042-01-01T00:00',
            'teardown_end' => '2042-01-01T00:00',
            'enable_day_of_event' => true,
        ]);

        $this->request = $this->request->withParsedBody($body)->withHeader('referer', 'http://localhost/test');
        $this->app->instance(Request::class, $this->request);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/test')
            ->willReturn($this->response);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        foreach (array_keys($body) as $field) {
            /** @var EventConfig $config */
            $config = EventConfig::find($field);
            $this->assertNotEmpty($config);
            $value = $config->value;
            if (in_array($field, ['buildup_start', 'event_start', 'event_end', 'teardown_end'])) {
                $value = Carbon::createFromDatetime($value)->format('Y-m-d\TH:i');
            }
            $this->assertEquals($body[$field], $value);
        }

        $this->assertTrue($this->log->hasInfoThatContains('Updated'));
        $this->assertHasNotification('config.edit.success');
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     */
    public function testSaveRemoveIfDefault(): void
    {
        (new EventConfig(['name' => 'baz', 'value' => 'foo']))->save();
        $this->config->set('baz', 'foo');
        $this->request->attributes->set('page', 'test');
        $this->request = $this->request->withParsedBody(array_merge($this->validTestBody, [
            'baz' => 'default value',
        ]));

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->auth->setPermissions(['some_test_permission', 'another_test_permission']);
        $controller->save($this->request);

        $this->assertNull(EventConfig::whereName('baz')->first());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     */
    public function testSaveWriteBack(): void
    {
        $this->app->instance('path.config', __DIR__ . '/Stub');
        $this->config->set(include $this->app->get('path.config') . '/app.php');

        (new EventConfig(['name' => 'write_to_file', 'value' => 'test']))->save();
        $this->config->set('write_to_file', 'stuff');
        $this->request->attributes->set('page', 'test');
        $this->request = $this->request->withParsedBody(array_merge($this->validTestBody, [
            'write_to_file' => 'new value',
            'normal_config' => 'normal value',
            'element_key' => '2042-02-03',
            'write_with_default' => 'saved value',
        ]));

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertNull(EventConfig::whereName('write_to_file')->first());
        $this->assertNull(EventConfig::whereName('another_write')->first());
        $this->assertNotNull(EventConfig::whereName('normal_config')->first());

        $content = file_get_contents($this->app->get('path.config') . '/config.local.php');
        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('return', $content);
        $this->assertStringContainsString('not edit', $content); // Warning about changes
        $this->assertStringContainsString('write_to_file', $content);
        $this->assertStringContainsString('new value', $content);
        $this->assertStringContainsString('\'element\'', $content);
        $this->assertStringContainsString('\'key\'', $content);
        $this->assertStringContainsString('\'2042-02-03\'', $content);
        $this->assertStringContainsString('write_with_default', $content);
        $this->assertStringContainsString('saved value', $content);
        $this->assertStringNotContainsString('normal_config', $content);
        $this->assertStringNotContainsString('another_write', $content);

        // Write additional config
        $this->request = $this->request->withParsedBody(array_merge($this->validTestBody, [
            'write_to_file' => 'new value',
            'another_write' => 'another value',
            'write_with_default' => 'default value',
        ]));
        $controller->save($this->request);

        $content = file_get_contents($this->app->get('path.config') . '/config.local.php');
        $this->assertStringContainsString('write_to_file', $content);
        $this->assertStringContainsString('new value', $content);
        $this->assertStringContainsString('another_write', $content);
        $this->assertStringContainsString('another value', $content);
        $this->assertStringNotContainsString('\'array-value\'', $content);
        $this->assertStringNotContainsString('write_with_default', $content);
        $this->assertStringNotContainsString('default value', $content);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::getOptions
     */
    public function testGetOptions(): void
    {
        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $data = $controller->getOptions();

        $this->assertArrayHasKey('test', $data);
        $this->assertArrayHasKey('invalid', $data);
        $this->assertArrayHasKey('event', $data);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->config->set('config_options', $this->options);
        $this->config->set('env_config', ['SOME_CONFIG_FROM_ENV' => 'I am the env!']);
        $this->config->set('password_min_length', 12);
        $this->request->attributes->set('page', 'event');

        $this->auth = $this->app->make(AccessibleAuthenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);
        $this->auth->setPermissions(['some_test_permission']);
        $this->app->instance('path.config', __DIR__ . '/Stub');
    }

    public function tearDown(): void
    {
        $configFile = __DIR__ . '/Stub/config.local.php';
        if (file_exists($configFile)) {
            unlink($configFile);
        }
    }
}
