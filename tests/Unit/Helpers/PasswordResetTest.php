<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Password;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class PasswordResetTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\Password::triggerPasswordReset
     */
    public function testTriggerPasswordReset(): void
    {
        $this->app->instance('config', new Config([]));

        $log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $log);

        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        $this->app->instance(EngelsystemMailer::class, $mailer);
        $this->setExpects($mailer, 'sendViewTranslated');

        $user = User::factory()->create();
        Password::triggerPasswordReset($user);

        $this->assertNotEmpty((new \Engelsystem\Models\User\PasswordReset())->find($user->id)->first());
        $this->assertTrue($log->hasInfoThatContains($user->name));
    }
}
