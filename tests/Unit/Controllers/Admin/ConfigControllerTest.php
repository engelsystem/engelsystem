<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\ConfigController;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\Controllers\ControllerTest;

class ConfigControllerTest extends ControllerTest
{
    protected array $options = [
        'config' => [
            'foo' => [
                'type' => 'text',
                'required' => 'true',
            ],
            'bar' => [
                'type' => 'datetime-local',
                'name' => 'some.bar',
            ],
        ],
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::index
     */
    public function testIndex(): void
    {
        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);

        $this->setExpects($this->response, 'redirectTo', ['http://localhost/admin/config/event'], $this->response);

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

                $this->assertArrayHasKey('name', $data['config']['foo']);
                $this->assertArrayHasKey('type', $data['config']['foo']);
                $this->assertArrayHasKey('required', $data['config']['foo']);
                $this->assertArrayHasKey('required_icon', $data['config']['foo']);
                $this->assertEquals('config.foo', $data['config']['foo']['name']);

                $this->assertArrayHasKey('name', $data['config']['bar']);
                $this->assertEquals('some.bar', $data['config']['bar']['name']);

                $this->assertArrayHasKey('test', $data['options']);
                $this->assertArrayHasKey('url', $data['options']['test']);
                $this->assertArrayHasKey('title', $data['options']['test']);
                $this->assertEquals('config.test', $data['options']['test']['title']);
                $this->assertEquals('http://localhost/admin/config/test', $data['options']['test']['url']);

                return $this->response;
            });

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class, ['options' => ['test' => $this->options]]);

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
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveCreateInvalid(): void
    {
        $this->request->attributes->set('page', 'event');
        $this->request = $this->request->withParsedBody(['buildup_start' => 'invalid date']);

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
    public function testSaveValidateEvent(): void
    {
        $this->request->attributes->set('page', 'event');
        $this->request = $this->request->withParsedBody([
            'event_end' => '2042-01-01T00:00',
            'teardown_end' => '2000-01-01T00:00',
        ]);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     */
    public function testSaveTestEmpty(): void
    {
        $this->request->attributes->set('page', 'test');

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class, ['options' => ['test' => $this->options]]);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ConfigController::save
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validation
     * @covers \Engelsystem\Controllers\Admin\ConfigController::validateEvent
     */
    public function testSaveEmpty(): void
    {
        $this->request->attributes->set('page', 'event');
        $body = [
            'name' => '',
            'welcome_msg' => '',
            'buildup_start' => '',
            'event_start' => '',
            'event_end' => '',
            'teardown_end' => '',
        ];

        $this->request = $this->request->withParsedBody($body);
        $this->app->instance(Request::class, $this->request);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/')
            ->willReturn($this->response);

        /** @var ConfigController $controller */
        $controller = $this->app->make(ConfigController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertEmpty(EventConfig::all());

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
        $this->request->attributes->set('page', 'event');
        $body = [
            'name' => 'TestEvent',
            'welcome_msg' => 'Welcome!',
            'buildup_start' => '2001-01-01T10:42',
            'event_start' => '2010-01-01T00:00',
            'event_end' => '2042-01-01T00:00',
            'teardown_end' => '2042-01-01T00:00',
        ];

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
}
