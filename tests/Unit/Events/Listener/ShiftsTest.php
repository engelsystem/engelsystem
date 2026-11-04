<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\DataEvent;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\Listener\Shifts;
use Engelsystem\Events\ModelEvent;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

#[CoversMethod(Shifts::class, '__construct')]
#[CoversMethod(Shifts::class, 'deletingShiftsList')]
#[CoversMethod(Shifts::class, 'deletingModelWithShifts')]
#[CoversMethod(Shifts::class, 'deletingModelWithShiftEntries')]
#[CoversMethod(Shifts::class, 'deletingShiftEntryCreateWorklog')]
#[CoversMethod(Shifts::class, 'deletingShiftEntrySendEmail')]
#[CoversMethod(Shifts::class, 'updatingSendEmail')]
#[AllowMockObjectsWithoutExpectations]
class ShiftsTest extends TestCase
{
    use HasDatabase;

    protected Authenticator | MockObject $auth;

    protected EventDispatcher | MockObject $event;

    protected TestLogger $log;

    protected EngelsystemMailer | MockObject $mailer;

    protected Shift $shift;

    protected ShiftEntry $entry;

    protected Translator | MockObject $translator;

    protected User $user;

    public function testDeletingShiftsList(): void
    {
        $anotherShift = Shift::factory([
            'title' => 'Foo',
            'start' => Carbon::now(),
            'end' => Carbon::now()->addHours(2),
        ])->create();

        $this->event->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (ModelEvent $event) use ($anotherShift) {
                $this->assertEquals('model.shifts.shift.deleting', $event->getName());
                $this->assertTrue(in_array($event->model->id, [$this->shift->id, $anotherShift->id]));
                $this->assertTrue($event->model->relationLoaded('shiftEntries'));
                return $this->event;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftsList(new DataEvent('shifts.deleting', [$this->shift, $anotherShift]));
    }

    public function testDeletingModelWithShifts(): void
    {
        $location = $this->shift->location;
        $this->event->expects($this->exactly(1))
            ->method('dispatch')
            ->willReturnCallback(function (DataEvent $event) {
                $this->assertEquals('shifts.deleting', $event->getName());
                $this->assertCount(1, $event->data);
                $this->assertEquals($event->data[0], Shift::find($this->shift->id));
                return $this->event;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingModelWithShifts(new ModelEvent('model.location.deleting', $location));
    }

    public function testDeletingModelWithShiftEntries(): void
    {
        $this->event->expects($this->exactly(1))
            ->method('dispatch')
            ->willReturnCallback(function (ModelEvent $event) {
                $this->assertEquals('model.shifts.shiftentry.deleting', $event->getName());
                $this->assertEquals($event->model, $this->shift->shiftEntries->first());
                return $this->event;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingModelWithShiftEntries(new ModelEvent('model.shifts.shift.deleting', $this->shift));
    }

    public function testDeletingShiftEntryCreateWorklogs(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);
        $this->setExpects($this->translator, 'translate', null, 'Text', $this->atLeastOnce());

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftEntryCreateWorklog(new ModelEvent('model.shifts.shift.deleting', $this->entry));

        $this->assertCount(1, $this->user->worklogs);
        $this->assertEquals($this->shift->isNightShift() ? 4 : 2, $this->user->worklogs[0]->hours);
        $this->assertEquals('Text', $this->user->worklogs[0]->description);

        $this->assertTrue($this->log->hasInfoThatContains('Created worklog entry'));
    }

    public function testDeletingShiftEntryCreateWorklogsIgnoreFreeload(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $this->entry->freeloaded_by = $user1->id;
        $this->entry->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftEntryCreateWorklog(new ModelEvent('model.shifts.shift.deleting', $this->entry));

        $this->assertCount(0, $this->user->worklogs);
    }

    public function testDeletingShiftEntryCreateWorklogsIgnoreNotStarted(): void
    {
        $this->shift->start = Carbon::now()->addMinutes(42);
        $this->shift->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftEntryCreateWorklog(new ModelEvent('model.shifts.shift.deleting', $this->entry));

        $this->assertCount(0, $this->user->worklogs);
    }

    public function testDeletingShiftEntrySendEmailNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $this->user->settings->email_shiftinfo = false;
        $this->user->settings->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftEntrySendEmail(new ModelEvent('model.shifts.shift.deleting', $this->entry));
    }

    public function testDeletingShiftEntrySendEmail(): void
    {
        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (User $user, string $subject, string $template, array $data): bool {
                $this->assertEquals($this->user->id, $user->id);
                $this->assertEquals('notification.shift.deleted', $subject);
                $this->assertEquals('emails/worklog-from-shift', $template);
                $this->assertArrayHasKey('shift', $data);
                $this->assertEquals($this->entry->shift, $data['shift']);
                $this->assertArrayHasKey('entry', $data);
                $this->assertEquals($this->entry->id, $data['entry']->id);

                return true;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingShiftEntrySendEmail(new ModelEvent('model.shifts.shift.deleting', $this->entry));
    }

    public function testUpdatedSendEmailNoRelevantChange(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $this->shift->description = 'Foo';

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatingSendEmail(new ModelEvent('model.shifts.shift.updating', $this->shift));
    }

    public function testUpdatedSendEmailNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $this->shift->title = 'Bar';

        $this->user->settings->email_shiftinfo = false;
        $this->user->settings->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatingSendEmail(new ModelEvent('model.shifts.shift.updating', $this->shift));
    }

    public function testUpdatedSendEmailAlreadyEnded(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $oldShift = Shift::find($this->shift->id);
        $oldShift->end = Carbon::now()->subMinutes(42);
        $oldShift->save();

        $this->shift->end = Carbon::now()->subMinutes(42);

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatingSendEmail(new ModelEvent('model.shifts.shift.updating', $this->shift));
    }

    public function testUpdatedSendEmail(): void
    {
        $oldShift = Shift::find($this->shift->id)
            ->load(['shiftType', 'location']);
        $this->shift->title = 'Bar';

        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (
                User $user,
                string $subject,
                string $template,
                array $data
            ) use ($oldShift): bool {
                $this->assertEquals($this->user->id, $user->id);
                $this->assertEquals('notification.shift.updated', $subject);
                $this->assertEquals('emails/updated-shift', $template);
                $this->assertArrayHasKey('shift', $data);
                $this->assertEquals($this->shift, $data['shift']);
                $this->assertArrayHasKey('oldShift', $data);
                $this->assertEquals($oldShift, $data['oldShift']);

                return true;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatingSendEmail(new ModelEvent('model.shifts.shift.updating', $this->shift));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->mailer = $this->createMock(EngelsystemMailer::class);
        $this->app->instance(EngelsystemMailer::class, $this->mailer);

        $config = new Config([
            'night_shifts' => [
                'enabled' => true,
                'start' => 2,
                'end' => 8,
                'multiplier' => 2,
            ],
        ]);
        $this->app->instance('config', $config);

        $this->translator = $this->createMock(Translator::class);
        $this->app->instance('translator', $this->translator);

        $this->user = User::factory()
            ->has(Settings::factory([
                'language' => '',
                'theme' => 1,
                'email_shiftinfo' => true,
            ]))
            ->create();

        $start = Carbon::now()->subHour();
        $this->shift = Shift::factory([
            'title' => 'Foo',
            'start' => $start,
            'end' => $start->copy()->addHours(2),
        ])->create();
        $this->entry = ShiftEntry::factory([
            'shift_id' => $this->shift->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => null,
        ])->create();

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance('authenticator', $this->auth);

        $this->event = $this->createMock(EventDispatcher::class);
        $this->app->instance(EventDispatcher::class, $this->event);
    }
}
