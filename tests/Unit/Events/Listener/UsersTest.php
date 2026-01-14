<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\Users;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;

class UsersTest extends TestCase
{
    use HasDatabase;

    protected TestLogger $log;

    /**
     * @covers \Engelsystem\Events\Listener\Users::created
     * @covers \Engelsystem\Events\Listener\Users::__construct
     */
    public function testCreated(): void
    {
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        /** @var User $user */
        $user = User::factory()->create();

        $mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (
                User $recipient,
                string $subject,
                string $template,
                array $data
            ) use ($user): bool {
                $this->assertEquals($user->id, $recipient->id);
                $this->assertEquals('email.user.welcome.subject', $subject);
                $this->assertEquals('emails/user-welcome', $template);
                $this->assertArrayHasKey('username', $data);
                $this->assertEquals($user->displayName, $data['username']);
                return true;
            });

        $handler = new Users($this->log, $mailer);
        $handler->created($user);
    }

    protected function setUp(): void
    {
        $this->log = new TestLogger();

        parent::setUp();
        $this->initDatabase();
        $this->app->instance('config', new Config());
    }
}
