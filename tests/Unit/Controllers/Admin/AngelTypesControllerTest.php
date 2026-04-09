<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\AngelTypesController;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class AngelTypesControllerTest extends ControllerTest
{
    protected Redirector|MockObject $redirect;

    /**
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::__construct
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::edit
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::showEdit
     */
    public function testEdit(): void
    {
        /** @var AngelTypesController $controller */
        $controller = $this->app->make(AngelTypesController::class);
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelTypes = AngelType::factory(3)->create();
        (new NeededAngelType([
            'angel_type_id' => $angelType->id,
            'angel_type_id' => $angelTypes[0]->id,
            'count' => 3,
        ]))->save();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($angelType) {
                $this->assertEquals('admin/angeltypes/edit', $view);
                $this->assertEquals($angelType->id, $data['angelType']?->id);
                $this->assertCount(3, $data['angel_types'] ?? []);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('angel_type_id', 1);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::edit
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::showEdit
     */
    public function testEditNew(): void
    {
        /** @var AngelTypesController $controller */
        $controller = $this->app->make(AngelTypesController::class);
        AngelType::factory(3)->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/angeltypes/edit', $view);
                $this->assertEmpty($data['angelType'] ?? []);
                $this->assertCount(3, $data['angel_types'] ?? []);
                return $this->response;
            });

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::save
     */
    public function testSave(): void
    {
        /** @var AngelTypesController $controller */
        $controller = $this->app->make(AngelTypesController::class);
        $controller->setValidator(new Validator());
        AngelType::factory(3)->create();

        $this->setExpects($this->redirect, 'to', ['/angeltypes']);

        $this->request = $this->request->withParsedBody([
            'name'         => 'Testlocation',
            'description'  => 'Something',
            'dect'         => 'DECTNR',
            'map_url'      => 'https://osm.url/#map=h/x/y',
            'angel_type_1' => '0',
            'angel_type_2' => '3',
        ]);

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Updated location'));
        $this->assertHasNotification('location.edit.success');
        $this->assertCount(1, AngelType::whereName('Testlocation')->get());

        $neededAngelType = NeededAngelType::whereAngelTypeId(1)
            ->where('angel_type_id', 2)
            ->where('count', 3)
            ->get();
        $this->assertCount(1, $neededAngelType);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::save
     */
    public function testSaveUniqueName(): void
    {
        /** @var AngelTypesController $controller */
        $controller = $this->app->make(AngelTypesController::class);
        $controller->setValidator(new Validator());
        AngelType::factory()->create(['name' => 'Testlocation']);

        $this->request = $this->request->withParsedBody([
            'name' => 'Testlocation',
        ]);

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\AngelTypesController::delete
     */
    public function testDelete(): void
    {
        /** @var EventDispatcher|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $dispatcher);
        /** @var AngelTypesController $controller */
        $controller = $this->app->make(AngelTypesController::class);
        $controller->setValidator(new Validator());
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create(['angel_type_id' => $angelType->id, 'start' => Carbon::create()->subHour()]);
        /** @var User $user */
        $user = User::factory()->create(['name' => 'foo', 'email' => 'lorem@ipsum']);
        /** @var ShiftEntry $shiftEntry */
        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'user_id' => $user->id]);

        $this->setExpects($this->redirect, 'to', ['/angeltypes'], $this->response);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (string $event, array $data) use ($angelType, $user) {
                $this->assertEquals('shift.deleting', $event);
                $this->assertEquals($angelType->id, $data['shift']->location->id);
                $this->assertEquals($user->id, $data['shift']->shiftEntries[0]->user->id);

                return [];
            });

        $this->request = $this->request->withParsedBody(['id' => 1, 'delete' => '1']);

        $controller->delete($this->request);

        $this->assertNull(AngelType::find($angelType->id));
        $this->assertTrue($this->log->hasInfoThatContains('Deleted location'));
        $this->assertHasNotification('location.delete.success');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirect);
    }
}
