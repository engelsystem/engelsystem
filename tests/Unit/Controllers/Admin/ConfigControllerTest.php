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

        // Save with additional permission
        $this->auth->setPermissions(['some_test_permission', 'another_test_permission']);
        $controller->save($this->request);
        $baz = EventConfig::whereName('baz')->first();
        $this->assertNotNull($baz);
        $this->assertEquals('New value', $baz->value);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveTestValidateSelectError(): void
    {
        $this->request->attributes->set('page', 'test');
        $data = $this->validTestBody;
        $data['selectable_option'] = 'not selectable';
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
    public function testSaveTestValidateMultiSelectErrorType(): void
    {
        $this->request->attributes->set('page', 'test');
        $data = $this->validTestBody;
        $data['multiselectable_option'] = 'not array';
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
    public function testSaveTestValidateMultiSelectErrorValue(): void
    {
        $this->request->attributes->set('page', 'test');
        $data = $this->validTestBody;
        $data['multiselectable_option'] = ['not_in_values'];
        $this->request = $this->request->withParsedBody($data);

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
            if ($value instanceof Carbon) {
                $value = $value->format('Y-m-d\TH:i');
            }
            $this->assertEquals($body[$field], $value);
        }

        $this->assertTrue($this->log->hasInfoThatContains('Updated'));
        $this->assertHasNotification('config.edit.success');
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
        $this->request->attributes->set('page', 'event');

        $this->auth = $this->app->make(AccessibleAuthenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);
        $this->auth->setPermissions(['some_test_permission']);
    }
}
