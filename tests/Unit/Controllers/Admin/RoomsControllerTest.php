<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\RoomsController;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class RoomsControllerTest extends ControllerTest
{
    protected Redirector|MockObject $redirect;

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::__construct
     * @covers \Engelsystem\Controllers\Admin\RoomsController::index
     */
    public function testIndex(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        Room::factory(5)->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/rooms/index', $view);
                $this->assertTrue($data['is_index'] ?? false);
                $this->assertCount(5, $data['rooms'] ?? []);
                return $this->response;
            });

        $controller->index();
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::edit
     * @covers \Engelsystem\Controllers\Admin\RoomsController::showEdit
     */
    public function testEdit(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        /** @var Room $room */
        $room = Room::factory()->create();
        $angelTypes = AngelType::factory(3)->create();
        (new NeededAngelType(['room_id' => $room->id, 'angel_type_id' => $angelTypes[0]->id, 'count' => 3]))->save();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($room) {
                $this->assertEquals('admin/rooms/edit', $view);
                $this->assertEquals($room->id, $data['room']?->id);
                $this->assertCount(3, $data['angel_types'] ?? []);
                $this->assertCount(1, $data['needed_angel_types'] ?? []);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('room_id', 1);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::edit
     * @covers \Engelsystem\Controllers\Admin\RoomsController::showEdit
     */
    public function testEditNew(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        AngelType::factory(3)->create();

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/rooms/edit', $view);
                $this->assertEmpty($data['room'] ?? []);
                $this->assertCount(3, $data['angel_types'] ?? []);
                $this->assertEmpty($data['needed_angel_types'] ?? []);
                return $this->response;
            });

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::save
     */
    public function testSave(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        $controller->setValidator(new Validator());
        AngelType::factory(3)->create();

        $this->setExpects($this->redirect, 'to', ['/admin/rooms']);

        $this->request = $this->request->withParsedBody([
            'name'         => 'Testroom',
            'description'  => 'Something',
            'dect'         => 'DECTNR',
            'map_url'      => 'https://osm.url/#map=h/x/y',
            'angel_type_1' => '0',
            'angel_type_2' => '3',
        ]);

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Updated room'));
        $this->assertHasNotification('room.edit.success');
        $this->assertCount(1, Room::whereName('Testroom')->get());

        $neededAngelType = NeededAngelType::whereRoomId(1)
            ->where('angel_type_id', 2)
            ->where('count', 3)
            ->get();
        $this->assertCount(1, $neededAngelType);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::save
     */
    public function testSaveUniqueName(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        $controller->setValidator(new Validator());
        Room::factory()->create(['name' => 'Testroom']);

        $this->request = $this->request->withParsedBody([
            'name' => 'Testroom',
        ]);

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::save
     * @covers \Engelsystem\Controllers\Admin\RoomsController::delete
     */
    public function testSaveDelete(): void
    {
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        $controller->setValidator(new Validator());
        /** @var Room $room */
        $room = Room::factory()->create();

        $this->request = $this->request->withParsedBody([
            'id'     => '1',
            'delete' => '1',
        ]);

        $controller->save($this->request);
        $this->assertEmpty(Room::find($room->id));
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\RoomsController::delete
     */
    public function testDelete(): void
    {
        /** @var EventDispatcher|MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $dispatcher);
        /** @var RoomsController $controller */
        $controller = $this->app->make(RoomsController::class);
        $controller->setValidator(new Validator());
        /** @var Room $room */
        $room = Room::factory()->create();
        /** @var Shift $shift */
        $shift = Shift::factory()->create(['room_id' => $room->id, 'start' => Carbon::create()->subHour()]);
        /** @var User $user */
        $user = User::factory()->create(['name' => 'foo', 'email' => 'lorem@ipsum']);
        /** @var ShiftEntry $shiftEntry */
        ShiftEntry::factory()->create(['shift_id' => $shift->id, 'user_id' => $user->id]);

        $this->setExpects($this->redirect, 'to', ['/admin/rooms'], $this->response);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (string $event, array $data) use ($room, $user) {
                $this->assertEquals('shift.entry.deleting', $event);
                $this->assertEquals($room->id, $data['room']->id);
                $this->assertEquals($user->id, $data['user']->id);

                return [];
            });

        $this->request = $this->request->withParsedBody(['id' => 1, 'delete' => '1']);

        $controller->delete($this->request);

        $this->assertNull(Room::find($room->id));
        $this->assertTrue($this->log->hasInfoThatContains('Deleted room'));
        $this->assertHasNotification('room.delete.success');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirect);
    }
}
