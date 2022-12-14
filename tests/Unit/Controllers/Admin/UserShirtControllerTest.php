<?php

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\UserShirtController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class UserShirtControllerTest extends ControllerTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\Admin\UserShirtController::editShirt
     * @covers \Engelsystem\Controllers\Admin\UserShirtController::__construct
     */
    public function testIndex(): void
    {
        $request = $this->request->withAttribute('user_id', 1);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Redirector|MockObject $redirector */
        $redirector = $this->createMock(Redirector::class);
        $user = new User();
        User::factory()->create();

        $this->setExpects($this->response, 'withView', ['admin/user/edit-shirt.twig'], $this->response);

        $controller = new UserShirtController($auth, $this->config, $this->log, $redirector, $this->response, $user);

        $controller->editShirt($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserShirtController::editShirt
     */
    public function testIndexUserNotFound(): void
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Redirector|MockObject $redirector */
        $redirector = $this->createMock(Redirector::class);
        $user = new User();

        $controller = new UserShirtController($auth, $this->config, $this->log, $redirector, $this->response, $user);

        $this->expectException(ModelNotFoundException::class);
        $controller->editShirt($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserShirtController::saveShirt
     */
    public function testSaveShirt(): void
    {
        $request = $this->request
            ->withAttribute('user_id', 1)
            ->withParsedBody([
                'shirt_size' => 'S',
            ]);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->config->set('tshirt_sizes', ['S' => 'Small']);
        /** @var Redirector|MockObject $redirector */
        $redirector = $this->createMock(Redirector::class);
        User::factory()
            ->has(State::factory())
            ->has(PersonalData::factory())
            ->create();

        $auth
            ->expects($this->exactly(4))
            ->method('can')
            ->with('admin_arrive')
            ->willReturnOnConsecutiveCalls(true, true, true, false);
        $this->setExpects($redirector, 'back', null, $this->response, $this->exactly(4));

        $controller = new UserShirtController(
            $auth,
            $this->config,
            $this->log,
            $redirector,
            $this->response,
            new User()
        );
        $controller->setValidator(new Validator());

        // Set shirt size
        $controller->saveShirt($request);

        $this->assertHasNotification('user.edit.success');
        $this->assertTrue($this->log->hasInfoThatContains('Updated user shirt state'));

        $user = User::find(1);
        $this->assertEquals('S', $user->personalData->shirt_size);
        $this->assertFalse($user->state->arrived);
        $this->assertFalse($user->state->active);
        $this->assertFalse($user->state->got_shirt);

        // Set active, arrived and got_shirt
        $request = $request
            ->withParsedBody([
                'shirt_size' => 'S',
                'arrived'    => '1',
                'active'     => '1',
                'got_shirt'  => '1',
            ]);

        $controller->saveShirt($request);

        $user = User::find(1);
        $this->assertTrue($user->state->active);
        $this->assertTrue($user->state->arrived);
        $this->assertTrue($user->state->got_shirt);

        // Shirt size not available
        $request = $request
            ->withParsedBody([
                'shirt_size' => 'L',
            ]);

        $controller->saveShirt($request);
        $user = User::find(1);
        $this->assertEquals('S', $user->personalData->shirt_size);

        // Not allowed changing arrived
        $request = $request
            ->withParsedBody([
                'shirt_size' => 'S',
                'arrived'    => '1',
            ]);

        $this->assertFalse($user->state->arrived);
        $controller->saveShirt($request);
        $user = User::find(1);
        $this->assertFalse($user->state->arrived);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserShirtController::saveShirt
     */
    public function testSaveShirtUserNotFound(): void
    {
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Redirector|MockObject $redirector */
        $redirector = $this->createMock(Redirector::class);
        $user = new User();

        $controller = new UserShirtController($auth, $this->config, $this->log, $redirector, $this->response, $user);

        $this->expectException(ModelNotFoundException::class);
        $controller->editShirt($this->request);
    }
}
