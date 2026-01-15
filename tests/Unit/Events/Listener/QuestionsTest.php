<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\Questions;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Group;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;

class QuestionsTest extends TestCase
{
    use HasDatabase;

    protected TestLogger $log;

    /**
     * @covers \Engelsystem\Events\Listener\Questions::created
     * @covers \Engelsystem\Events\Listener\Questions::__construct
     */
    public function testCreated(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);

        // Create a user who can answer questions
        $admin = User::factory()
            ->has(Settings::factory())
            ->create();

        // Create the question.edit privilege and a group with it
        $privilege = Privilege::create(['name' => 'question.edit', 'description' => 'Answer questions']);
        $group = Group::create(['name' => 'Question Answerers']);
        $group->privileges()->attach($privilege);
        $admin->groups()->attach($group);

        // Create a question by a different user
        $asker = User::factory()->create();
        $question = Question::factory([
            'user_id' => $asker->id,
            'text' => 'How do I sign up?',
        ])->create();

        $mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (
                User $recipient,
                string $subject,
                string $template,
                array $data
            ) use ($admin, $question): bool {
                $this->assertEquals($admin->id, $recipient->id);
                $this->assertEquals('notification.question.new', $subject);
                $this->assertEquals('emails/question-new', $template);
                $this->assertEquals($question->id, $data['question']->id);
                return true;
            });

        $handler = new Questions($this->log, $mailer);
        $handler->created($question);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Questions::created
     */
    public function testCreatedSelfQuestion(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);

        // Create a user who can answer questions
        $admin = User::factory()
            ->has(Settings::factory())
            ->create();

        // Give them the permission
        $privilege = Privilege::create(['name' => 'question.edit', 'description' => 'Answer questions']);
        $group = Group::create(['name' => 'Question Answerers']);
        $group->privileges()->attach($privilege);
        $admin->groups()->attach($group);

        // Admin asks a question themselves - should not be notified
        $question = Question::factory([
            'user_id' => $admin->id,
            'text' => 'Testing my own question',
        ])->create();

        $mailer->expects($this->never())->method('sendViewTranslated');

        $handler = new Questions($this->log, $mailer);
        $handler->created($question);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Questions::created
     */
    public function testCreatedNoAdmins(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);

        // No users have question.edit permission
        $asker = User::factory()->create();
        $question = Question::factory([
            'user_id' => $asker->id,
            'text' => 'How do I sign up?',
        ])->create();

        $mailer->expects($this->never())->method('sendViewTranslated');

        $handler = new Questions($this->log, $mailer);
        $handler->created($question);
    }

    protected function setUp(): void
    {
        $this->log = new TestLogger();

        parent::setUp();
        $this->initDatabase();
        $this->app->instance('config', new Config());
    }
}
