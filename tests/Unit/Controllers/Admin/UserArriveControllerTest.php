<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\UserArriveController;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\HasDatabase;

class UserArriveControllerTest extends ControllerTest
{
    use HasDatabase;

    protected UserArriveController $controller;
    protected User $user;
    protected Redirector $redirect;

    /**
     * @covers \Engelsystem\Controllers\Admin\UserArriveController::saveArrive
     */
    public function testSaveArriveResetAction(): void
    {
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withParsedBody([
                'action' => 'reset',
            ])
            ->withHeader('accept', 'application/json');

        $this->setExpects($this->response, 'withHeader', ['content-type', 'application/json'], $this->response);
        $this->response->expects($this->once())
            ->method('withContent')->willReturnCallback(function ($data) {
                $data = json_decode($data, true);
                $this->assertFalse($data['state']);
                $this->assertEquals('-', $data['arrival_date']);
                return $this->response;
            });

        $this->controller->saveArrive($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserArriveController::saveArrive
     */
    public function testSaveArriveArriveAction(): void
    {
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withParsedBody([
                'action' => 'arrive',
            ])
            ->withHeader('accept', 'application/json');

        $this->setExpects($this->response, 'withHeader', ['content-type', 'application/json'], $this->response);
        $this->response->expects($this->once())
            ->method('withContent')->willReturnCallback(function ($data) {
                $data = json_decode($data, true);
                $this->assertTrue($data['state']);
                $this->assertNotEmpty($data['arrival_date']);
                return $this->response;
            });

        $this->controller->saveArrive($request);
    }
    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        $this->mockTranslator(fn(string $key, array $replace = []) => $key == 'general.date' ? 'Y-m-d' : $key);

        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirect);

        $this->user = User::factory()->create();

        $this->controller = $this->app->make(UserArriveController::class);
        $this->controller->setValidator(new Validator());
    }
}
