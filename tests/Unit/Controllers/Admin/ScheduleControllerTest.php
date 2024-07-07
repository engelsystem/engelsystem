<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\ScheduleController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Schedule\Event as EventData;
use Engelsystem\Helpers\Schedule\Room as RoomData;
use Engelsystem\Helpers\Schedule\Schedule as ScheduleData;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\HasDatabase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class ScheduleControllerTest extends ControllerTest
{
    use HasDatabase;
    use HasUserNotifications;

    protected AngelType $angelType;
    protected EventDispatcher | MockObject $event;
    protected Location $location;
    protected Shift $oldShift;
    protected Redirector | MockObject $redirect;
    protected Schedule $schedule;
    protected string $scheduleFile = __DIR__ . '/../../Helpers/Schedule/Assets/schedule-multiple.xml';
    protected ShiftType $shiftType;
    protected User $user;
    protected Validator $validator;

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::index
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::__construct
     */
    public function testIndex(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/schedule/index', $view);
                $this->assertArrayHasKey('schedules', $data);
                /** @var Schedule[] $schedules */
                $schedules = $data['schedules'];
                $this->assertNotEmpty($schedules);
                $this->assertEquals('Foo Schedule', $schedules[0]->name);
                return $this->response;
            });

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->index();

        $this->assertEquals($this->response, $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::edit
     */
    public function testEdit(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/schedule/edit', $view);
                $this->assertArrayHasKey('schedule', $data);
                /** @var Schedule $schedule */
                $schedule = $data['schedule'];
                $this->assertNotEmpty($schedule);
                $this->assertEquals('Foo Schedule', $schedule->name);
                return $this->response;
            });

        $request = $this->request->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->edit($request);

        $this->assertEquals($this->response, $response);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::save
     */
    public function testSaveNew(): void
    {
        $newId = $this->schedule->id + 1;
        $this->setExpects($this->redirect, 'to', ['/admin/schedule/load/' . $newId], $this->response);

        $request = Request::create('', 'POST', [
            'name' => 'Name',
            'url' => 'https://example.test/schedule.xml',
            'shift_type' => $this->shiftType->id,
            'location_' . $this->location->id => 1,
            'minutes_before' => 20,
            'minutes_after' => 25,
        ]);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $controller->setValidator($this->validator);
        $response = $controller->save($request);

        $this->assertEquals($this->response, $response);
        $schedule = Schedule::find($newId);
        $this->assertNotNull($schedule);
        $this->assertEquals('Name', $schedule->name);
        $this->assertEquals('https://example.test/schedule.xml', $schedule->url);
        $this->assertEquals($this->shiftType->id, $schedule->shift_type);
        $this->assertEquals(20, $schedule->minutes_before);
        $this->assertEquals(25, $schedule->minutes_after);
        $this->assertCount(1, $schedule->activeLocations);
        /** @var Location $location */
        $location = $schedule->activeLocations->first();
        $this->assertEquals($this->location->id, $location->id);

        $this->assertHasNotification('schedule.edit.success');
        $this->assertTrue($this->log->hasInfoThatContains('Schedule {name}'));
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::save
     */
    public function testSaveEdit(): void
    {
        $this->setExpects($this->redirect, 'to', ['/admin/schedule/load/' . $this->schedule->id], $this->response);
        $shiftType = ShiftType::factory()->create();

        $request = Request::create('', 'POST', [
            'name' => 'New name',
            'url' => 'https://test.example/schedule.xml',
            'shift_type' => $shiftType->id,
            'minutes_before' => 10,
            'minutes_after' => 5,
        ])
            ->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $controller->setValidator($this->validator);
        $response = $controller->save($request);

        $this->assertEquals($this->response, $response);
        $schedule = Schedule::find($this->schedule->id);
        $this->assertNotNull($schedule);
        $this->assertEquals('New name', $schedule->name);
        $this->assertEquals('https://test.example/schedule.xml', $schedule->url);
        $this->assertEquals($shiftType->id, $schedule->shift_type);
        $this->assertEquals(10, $schedule->minutes_before);
        $this->assertEquals(5, $schedule->minutes_after);

        $this->assertHasNotification('schedule.edit.success');
        $this->assertTrue($this->log->hasInfoThatContains('Schedule {name}'));
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::save
     */
    public function testSaveInvalidShiftType(): void
    {
        $request = Request::create('', 'POST', [
            'name' => 'Test',
            'url' => 'https://test.example',
            'shift_type' => 1337,
            'minutes_before' => 0,
            'minutes_after' => 0,
        ]);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $controller->setValidator($this->validator);

        $this->expectException(ModelNotFoundException::class);
        $controller->save($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::save
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::delete
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::fireDeleteShiftEvents
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::deleteEvent
     */
    public function testSaveDelete(): void
    {
        $this->setExpects($this->redirect, 'to', ['/admin/schedule'], $this->response);

        $this->event->expects($this->exactly(3))
            ->method('dispatch')
            ->with('shift.deleting')
            ->willReturn([]);

        $request = Request::create('', 'POST', ['delete' => 'yes'])
            ->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->save($request);

        $this->assertEquals($this->response, $response);
        $this->assertNull(Schedule::find($this->schedule->id));

        $this->assertHasNotification('schedule.delete.success');
        $this->assertTrue($this->log->hasInfoThatContains('Deleted schedule ({schedule}) shift'));
        $this->assertTrue($this->log->hasInfoThatContains('Schedule {name}'));
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::loadSchedule
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleData
     */
    public function testLoadSchedule(): void
    {
        $this->setScheduleResponses([new Response(file_get_contents($this->scheduleFile))]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/schedule/load', $view);

                $this->assertArrayHasKey('schedule', $data);
                /** @var ScheduleData $scheduleData */
                $scheduleData = $data['schedule'];
                $this->assertInstanceOf(ScheduleData::class, $scheduleData);

                $this->assertArrayHasKey('locations', $data);
                $this->assertArrayHasKey('add', $data['locations']);
                /** @var RoomData[] $roomData */
                $roomData = $data['locations']['add'];
                $this->assertNotEmpty($roomData);
                $this->assertInstanceOf(RoomData::class, $roomData[0]);

                $this->assertArrayHasKey('shifts', $data);
                foreach (['add', 'update', 'delete'] as $type) {
                    $this->assertArrayHasKey($type, $data['shifts']);
                    /** @var EventData[] $eventData */
                    $eventData = $data['shifts'][$type];
                    $this->assertNotEmpty($eventData);
                    $this->assertInstanceOf(EventData::class, $eventData[array_key_first($eventData)]);
                }
                return $this->response;
            });

        $request = Request::create('', 'POST')
            ->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->loadSchedule($request);

        $this->assertEquals($this->response, $response);
    }

    public function loadScheduleErrorsData(): array
    {
        return [
            // Server error
            [new RequestException('Error Communicating with Server', new Request()), 'schedule.import.request-error'],
            // Not found
            [new Response('', 202), 'schedule.import.request-error', true],
            // Decoding error
            [new Response(''), 'schedule.import.read-error', true],
        ];
    }

    /**
     * @covers       \Engelsystem\Controllers\Admin\ScheduleController::loadSchedule
     * @covers       \Engelsystem\Controllers\Admin\ScheduleController::getScheduleData
     * @dataProvider loadScheduleErrorsData
     */
    public function testScheduleResponseErrors(object $request, string $notification, bool $logWarning = false): void
    {
        $this->setScheduleResponses([$request]);
        $this->setExpects($this->redirect, 'back', null, $this->response);

        $request = Request::create('', 'POST')
            ->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->loadSchedule($request);

        $this->assertEquals($this->response, $response);
        $this->assertHasNotification($notification, NotificationType::ERROR);

        if ($logWarning) {
            $this->assertTrue($this->log->hasWarningThatContains(' during schedule '));
        } else {
            $this->assertTrue($this->log->hasErrorThatContains(' during schedule '));
        }
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleData
     */
    public function testGetScheduleDataScheduleNotFound(): void
    {
        $request = Request::create('', 'POST', ['delete' => 'yes'])
            ->withAttribute('schedule_id', 42);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);

        $this->expectException(ModelNotFoundException::class);
        $controller->loadSchedule($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::importSchedule
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleData
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::patchSchedule
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::newRooms
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::shiftsDiff
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleShiftsByGuid
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleShiftsWhereNotGuid
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::eventFromScheduleShift
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::fireUpdateShiftUpdateEvent
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getAllLocations
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::createLocation
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::createEvent
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::updateEvent
     */
    public function testImportSchedule(): void
    {
        $this->setScheduleResponses([new Response(file_get_contents($this->scheduleFile))]);
        $this->setExpects($this->redirect, 'to', ['/admin/schedule'], $this->response);

        $request = Request::create('', 'POST')
            ->withAttribute('schedule_id', $this->schedule->id);

        $this->event->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(['shift.updating'], ['shift.deleting'])
            ->willReturn([]);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->importSchedule($request);

        $this->assertEquals($this->response, $response);

        $this->assertTrue($this->log->hasInfoThatContains('Started schedule'));
        $this->assertTrue($this->log->hasInfoThatContains('Created schedule location'));
        $this->assertTrue($this->log->hasInfoThatContains('Created schedule ({schedule}) shift'));
        $this->assertTrue($this->log->hasInfoThatContains('Updated schedule ({schedule}) shift'));
        $this->assertTrue($this->log->hasInfoThatContains('Deleted schedule ({schedule}) shift'));
        $this->assertTrue($this->log->hasInfoThatContains('Ended schedule'));

        $this->assertHasNotification('schedule.import.success');

        $this->assertCount(4, $this->schedule->shifts);
        $location = Location::whereName('Example Room')->get();
        $this->assertCount(1, $location);
        $location2 = Location::whereName('Another Room')->get();
        $this->assertCount(1, $location2);
        $location3 = Location::whereName('Third Room with a very looooong tit')->get();
        $this->assertCount(1, $location3);
        /** @var Location $location */
        $location = $location->first();
        /** @var Location $location2 */
        $location2 = $location2->first();
        /** @var Location $location3 */
        $location3 = $location3->first();

        $this->assertCount(1, $location->shifts);
        $this->assertCount(3, $location2->shifts);
        $this->assertCount(0, $location3->shifts);

        // Deleted shift
        $this->assertNull(Shift::find($this->oldShift->id));

        // Updated shift
        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = ScheduleShift::whereGuid('3e896c59-0d90-4817-8f74-af7fbb758f32')->first();
        $this->assertNotEmpty($scheduleShift);
        $shift = $scheduleShift->shift;
        $this->assertEquals('First event [DE]', $shift->title);
        $this->assertEquals('https://example.com/first-1-event', $shift->url);
        $this->assertEquals('2042-10-02 09:45:00', $shift->start->toDateTimeString());
        $this->assertEquals('2042-10-02 11:45:00', $shift->end->toDateTimeString());
        $this->assertEquals($this->shiftType->id, $shift->shift_type_id);
        $this->assertEquals($location->id, $shift->location_id);
        $this->assertEquals($this->user->id, $shift->updated_by);

        // Created shift
        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = ScheduleShift::whereGuid('6e662ec5-d18d-417d-8719-360a416bb153')->first();
        $this->assertNotEmpty($scheduleShift);
        $shift = $scheduleShift->shift;
        $this->assertEquals('Third event', $shift->title);
        $this->assertEquals('https://example.com/third-3-event', $shift->url);
        $this->assertEquals('2042-10-02 10:45:00', $shift->start->toDateTimeString());
        $this->assertEquals('2042-10-02 11:45:00', $shift->end->toDateTimeString());
        $this->assertEquals($this->shiftType->id, $shift->shift_type_id);
        $this->assertEquals($location2->id, $shift->location_id);
        $this->assertEquals($this->user->id, $shift->created_by);
        $this->assertNull($shift->updated_by);

        // Truncated shift name
        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = ScheduleShift::whereGuid('c6999865-5329-43f2-8aca-85ae39932d09')->first();
        /** @var Shift $shift */
        $shift = $scheduleShift->shift;
        $this->assertStringNotContainsString('such a big thing', $shift->title);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::importSchedule
     * @covers \Engelsystem\Controllers\Admin\ScheduleController::getScheduleData
     */
    public function testImportScheduleError(): void
    {
        $this->setScheduleResponses([new Response('', 404)]);
        $this->setExpects($this->redirect, 'back', null, $this->response);

        $request = Request::create('', 'POST')
            ->withAttribute('schedule_id', $this->schedule->id);

        /** @var ScheduleController $controller */
        $controller = $this->app->make(ScheduleController::class);
        $response = $controller->importSchedule($request);

        $this->assertEquals($this->response, $response);
        $this->assertHasNotification('schedule.import.request-error', NotificationType::ERROR);
    }

    protected function setScheduleResponses(array $queue): void
    {
        $handler = new MockHandler($queue);
        $guzzle = new Client(['handler' => HandlerStack::create($handler)]);
        $this->app->instance(Client::class, $guzzle);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new Validator();

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance('redirect', $this->redirect);

        $this->event = $this->createMock(EventDispatcher::class);
        $this->app->instance('events.dispatcher', $this->event);

        $this->shiftType = ShiftType::factory()->create();
        $this->location = Location::factory()->create(['name' => 'Example Room']);
        $location3 = Location::factory()->create(['name' => 'Another Room']);
        $this->angelType = AngelType::factory()->create();

        $this->schedule = Schedule::factory()->create([
            'shift_type' => $this->shiftType->id,
            'name' => 'Foo Schedule',
            'needed_from_shift_type' => false,
        ]);
        $this->schedule->activeLocations()->attach($this->location);
        $this->schedule->activeLocations()->attach($location3);
        /** @var Shift[] $shifts */
        $shifts = Shift::factory(3)->create([
            'location_id' => $this->location->id,
            'shift_type_id' => $this->shiftType->id,
        ]);
        foreach ($shifts as $shift) {
            (new ScheduleShift([
                'shift_id' => $shift->id,
                'schedule_id' => $this->schedule->id,
                'guid' => Uuid::uuid(),
            ]))->save();
        }
        $this->oldShift = $shifts[1];

        /** @var ScheduleShift $firstScheduleShift */
        $firstScheduleShift = ScheduleShift::query()->first();
        $firstScheduleShift->guid = '3e896c59-0d90-4817-8f74-af7fbb758f32';
        $firstScheduleShift->save();

        $firstShift = $firstScheduleShift->shift;
        $firstShift->neededAngelTypes()->create(['angel_type_id' => $this->angelType->id, 'count' => 3]);

        $this->user = User::factory()->create();
        // Shift from import
        ShiftEntry::factory()->create([
            'shift_id' => $firstShift->id,
            'angel_type_id' => $this->angelType->id,
            'user_id' => $this->user->id,
        ]);
        // Shift from some previous import
        ShiftEntry::factory()->create([
            'shift_id' => $this->oldShift->id,
            'angel_type_id' => $this->angelType->id,
            'user_id' => $this->user->id,
        ]);

        $authenticator = $this->createMock(Authenticator::class);
        $this->setExpects($authenticator, 'user', null, $this->user, $this->any());
        $this->app->instance('authenticator', $authenticator);
    }
}
