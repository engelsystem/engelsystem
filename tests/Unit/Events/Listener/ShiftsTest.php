<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\Shifts;
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
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class ShiftsTest extends TestCase
{
    use HasDatabase;

    protected Authenticator | MockObject $auth;

    protected TestLogger $log;

    protected EngelsystemMailer | MockObject $mailer;

    protected Shift $shift;

    protected ShiftEntry $entry;

    protected Translator | MockObject $translator;

    protected User $user;

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::deletingCreateWorklogs
     * @covers \Engelsystem\Events\Listener\Shifts::__construct
     */
    public function testDeletingCreateWorklogs(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user);
        $this->setExpects($this->translator, 'translate', null, 'Text', $this->atLeastOnce());

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingCreateWorklogs($this->shift);

        $this->assertCount(1, $this->user->worklogs);
        $this->assertEquals($this->shift->isNightShift() ? 4 : 2, $this->user->worklogs[0]->hours);
        $this->assertEquals('Text', $this->user->worklogs[0]->description);

        $this->assertTrue($this->log->hasInfoThatContains('Created worklog entry'));
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::deletingCreateWorklogs
     */
    public function testDeletingCreateWorklogsIgnoreFreeload(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $this->entry->freeloaded_by = $user1->id;
        $this->entry->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingCreateWorklogs($this->shift);

        $this->assertCount(0, $this->user->worklogs);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::deletingCreateWorklogs
     */
    public function testDeletingCreateWorklogsIgnoreNotStarted(): void
    {
        $this->shift->start = Carbon::now()->addMinutes(42);
        $this->shift->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingCreateWorklogs($this->shift);

        $this->assertCount(0, $this->user->worklogs);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::deletingSendEmails
     */
    public function testDeletingSendEmailsNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $this->user->settings->email_shiftinfo = false;
        $this->user->settings->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingSendEmails($this->shift);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::deletingSendEmails
     */
    public function testDeletingSendEmails(): void
    {
        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (User $user, string $subject, string $template, array $data): bool {
                $this->assertEquals($this->user->id, $user->id);
                $this->assertEquals('notification.shift.deleted', $subject);
                $this->assertEquals('emails/worklog-from-shift', $template);
                $this->assertArrayHasKey('shift', $data);
                $this->assertEquals($this->shift, $data['shift']);
                $this->assertArrayHasKey('entry', $data);
                $this->assertEquals($this->entry->id, $data['entry']->id);

                return true;
            });

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->deletingSendEmails($this->shift);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::updatedSendEmail
     */
    public function testUpdatedSendEmailNoRelevantChange(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $oldShift = Shift::find($this->shift->id);
        $this->shift->description = 'Foo';

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatedSendEmail($this->shift, $oldShift);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::updatedSendEmail
     */
    public function testUpdatedSendEmailNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $oldShift = Shift::find($this->shift->id);
        $this->shift->title = 'Bar';

        $this->user->settings->email_shiftinfo = false;
        $this->user->settings->save();

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatedSendEmail($this->shift, $oldShift);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::updatedSendEmail
     */
    public function testUpdatedSendEmailAlreadyEnded(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        $oldShift = Shift::find($this->shift->id);
        $oldShift->end = Carbon::now()->subMinutes(42);
        $this->shift->end = Carbon::now()->subMinutes(42);

        /** @var Shifts $listener */
        $listener = $this->app->make(Shifts::class);
        $listener->updatedSendEmail($this->shift, $oldShift);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Shifts::updatedSendEmail
     */
    public function testUpdatedSendEmail(): void
    {
        $oldShift = Shift::find($this->shift->id);
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
        $listener->updatedSendEmail($this->shift, $oldShift);
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
    }
}
