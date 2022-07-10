<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\Messages;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mailer\Exception\TransportException;

class MessagesTest extends TestCase
{
    use HasDatabase;

    protected TestLogger $log;

    /**
     * @covers \Engelsystem\Events\Listener\Messages::created
     * @covers \Engelsystem\Events\Listener\Messages::__construct
     * @covers \Engelsystem\Events\Listener\Messages::sendMail
     */
    public function testCreated(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        /** @var User $user */
        $user = User::factory()
            ->has(Settings::factory([
                'email_messages' => true,
            ]))
            ->create();
        $message = Message::factory()->create(['receiver_id' => $user->id]);

        $mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (
                User $receiver,
                string $subject,
                string $template,
                array $data
            ) use ($user): void {
                $this->assertEquals($user->id, $receiver->id);
                $this->assertEquals('notification.messages.new', $subject);
                $this->assertEquals('emails/messages-new', $template);
                $this->assertArrayHasKey('username', $data);
                $this->assertArrayHasKey('sender', $data);
                $this->assertArrayHasKey('send_message', $data);
            });

        $handler = new Messages($this->log, $mailer);
        $handler->created($message);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Messages::created
     */
    public function testCreatedNoEmail(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        /** @var User $user */
        $user = User::factory()
            ->has(Settings::factory([
                'email_messages' => false,
            ]))
            ->create();
        $message = Message::factory()->create(['receiver_id' => $user->id]);
        $mailer->expects($this->never())->method('sendViewTranslated');

        $handler = new Messages($this->log, $mailer);
        $handler->created($message);
    }

    /**
     * @covers \Engelsystem\Events\Listener\Messages::sendMail
     */
    public function testSendMailExceptionHandling(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        /** @var User $user */
        $user = User::factory()
            ->has(Settings::factory([
                'email_messages' => true,
            ]))
            ->create();
        $message = Message::factory()->create(['receiver_id' => $user->id]);
        $mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (): void {
                throw new TransportException();
            });

        $handler = new Messages($this->log, $mailer);

        $handler->created($message);
        $this->assertTrue($this->log->hasErrorThatContains('Unable to send email'));
    }

    protected function setUp(): void
    {
        $this->log = new TestLogger();

        parent::setUp();
        $this->initDatabase();
        $this->app->instance('config', new Config());
    }
}
