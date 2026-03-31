<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\Admin\AngelTypesController;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

#[CoversClass(AngelTypesController::class)]
#[AllowMockObjectsWithoutExpectations]
class AngelTypesControllerTest extends ControllerTestCase
{
    protected Redirector|MockObject $redirect;
    protected Authenticator|MockObject $auth;
    protected User $user;
    protected User $supporter;
    protected AngelType $angelType;
    protected AngelTypesController $controller;


    public function testHasPermission(): void
    {
        $response = $this->createMock(Response::class);
        $request = (new Request())->withAttribute('angel_type_id', $this->angelType->id);

        $controller = new AngelTypesController(
            $response,
            $this->app->get(Config::class),
            $this->auth,
            $this->angelType,
            new NullLogger(),
            $this->redirect
        );
        $this->assertFalse($controller->hasPermission($request, 'save'));
        $this->assertFalse($controller->hasPermission($request, 'edit'));

        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true, $this->atLeastOnce());
        $this->assertTrue($controller->hasPermission($request, 'save'));
        $this->assertTrue($controller->hasPermission($request, 'edit'));
    }

    public function testEdit(): void
    {
        $angelType = $this->angelType;
        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($angelType) {
                $this->assertEquals('admin/angeltypes/edit', $view);
                $this->assertFalse($this->user->isAngelTypeSupporter($angelType));
                $this->assertEquals($angelType->id, $data['angelType']?->id);
                $this->assertFalse($data['isSupporter']);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('angel_type_id', $angelType->id);

        $this->controller->edit($this->request);
    }

    public function testEditNew(): void
    {
        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/angeltypes/edit', $view);
                $this->assertEmpty($data['angelType'] ?? []);
                $this->assertEquals(false, $data['isSupporter']);
                return $this->response;
            });

        $this->controller->edit($this->request);
    }

    public function testSave(): void
    {
        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true, $this->any());

        $this->setExpects($this->redirect, 'to', ['/angeltypes']);

        $this->request = $this->request->withParsedBody([
            'name' => 'SomeTestAngelType',
            'description' => 'Something',
            'contact_name' => 'Foo',
            'contact_dect' => 'DECTNR',
            'contact_email' => '42@example.invalid',
            'restricted' => 'checked',
            'shift_self_signup' => 'checked',
            'show_on_dashboard' => 'checked',
            'hide_register' => 'checked',
        ]);

        $this->controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('angel type'));
        $this->assertHasNotification('angeltype.edit.success');
    }

    public function testSaveWithConfig(): void
    {
        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true, $this->any());
        $this->config->set('driving_license_enabled', true);
        $this->config->set('ifsg_enabled', true);

        $this->setExpects($this->redirect, 'to', ['/angeltypes']);

        $this->request = $this->request->withParsedBody([
            'name' => 'SomeTestAngelType',
            'description' => 'Something',
            'contact_name' => 'Foo',
            'contact_dect' => 'DECTNR',
            'contact_email' => '42@example.invalid',
            'restricted' => 'checked',
            'shift_self_signup' => 'checked',
            'show_on_dashboard' => 'checked',
            'hide_register' => 'checked',
            'requires_driver_license' => 'checked',
            'requires_ifsg_certificate' => 'checked',
        ]);

        $this->controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('angel type'));
        $this->assertHasNotification('angeltype.edit.success');
    }

    public function testSaveWithoutPermission(): void
    {
        $this->request = $this->request->withParsedBody([
            'name' => 'SomeTestAngelType',
            'description' => 'Something',
            'contact_name' => 'Foo',
            'contact_dect' => 'DECTNR',
            'contact_email' => '42@example.invalid',
            'restricted' => 'checked',
            'shift_self_signup' => 'checked',
            'show_on_dashboard' => 'checked',
            'hide_register' => 'checked',
        ]);

        $this->expectException(HttpForbidden::class);
        $this->controller->save($this->request);
    }

    public function testSaveUniqueName(): void
    {
        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true, $this->any());
        AngelType::factory()->create(['name' => 'TestAngelType']);

        $this->request = $this->request->withParsedBody([
            'name' => 'TestAngelType',
        ]);

        $this->expectException(ValidationException::class);
        $this->controller->save($this->request);
    }

    public function testDelete(): void
    {
        /** @var EventDispatcher|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $dispatcher);
        /** @var ShiftEntry $shiftEntry */
        ShiftEntry::factory()->create(['user_id' => $this->user->id, 'angel_type_id' => $this->angelType->id]);
        $angelType = $this->angelType;
        $user = $this->user;

        $this->setExpects($this->auth, 'can', ['angeltypes.edit'], true, $this->any());
        $this->setExpects($this->redirect, 'to', ['/angeltypes'], $this->response);

        $dispatcher->expects($this->atLeastOnce())
            ->method('dispatch')
            ->willReturnCallback(function (string $event, array $data) use ($angelType, $user) {
                $this->assertEquals('shift.entry.deleting', $event);
                $this->assertEquals($angelType->id, $data['entry']->angelType->id);
                $this->assertEquals($user->id, $data['entry']->user->id);

                return [];
            });

        $this->request = $this->request->withParsedBody(['id' => 1, 'delete' => '1']);

        $this->controller->delete($this->request);

        $this->assertNull(AngelType::find($angelType->id));
        $this->assertTrue($this->log->hasInfoThatContains('Deleted angel type'));
        $this->assertHasNotification('angeltype.delete.success');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->redirect = $this->createMock(Redirector::class);
        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Redirector::class, $this->redirect);
        $this->app->instance(Authenticator::class, $this->auth);
        $this->controller = $this->app->make(AngelTypesController::class);
        $this->controller->setValidator(new Validator());

        $this->user = User::factory()->create();
        $this->angelType = AngelType::factory()->create();
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
    }
}
