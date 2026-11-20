<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Confirmation;
use Engelsystem\Helpers\JWT;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

#[CoversMethod(Confirmation::class, '__construct')]
#[CoversMethod(Confirmation::class, 'confirmationAvailable')]
#[CoversMethod(Confirmation::class, 'sendEmailConfirmationLink')]
#[CoversMethod(Confirmation::class, 'getEmailConfirmationLink')]
#[CoversMethod(Confirmation::class, 'confirmEmailAddress')]
#[AllowMockObjectsWithoutExpectations]
class ConfirmationTest extends TestCase
{
    use HasDatabase;

    protected Config $config;
    protected TestLogger $log;
    protected EngelsystemMailer&MockObject $mailer;

    public function testConfirmationAvailable(): void
    {
        $confirm = $this->app->make(Confirmation::class);

        $this->assertTrue($confirm->confirmationAvailable());

        $this->config->set('email_confirmation', false);
        $this->assertFalse($confirm->confirmationAvailable());

        $this->config->set([
            'url' => null,
            'app_key' => null,
            'email_confirmation' => true,
        ]);
        $this->assertFalse($confirm->confirmationAvailable());
    }

    public function testSendEmailConfirmationLink(): void
    {
        $someUser = User::factory()->create();

        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (
                User $user,
                string $subject,
                string $template,
                array $data
            ) use ($someUser): bool {
                $this->assertEquals($someUser, $user);
                $this->assertEquals('email.user.email-confirmation', $subject);
                $this->assertEquals('emails/user-email-confirmation', $template);
                $this->assertArrayHasKey('user', $data);
                $this->assertArrayHasKey('username', $data);
                $this->assertArrayHasKey('url', $data);
                $this->assertEquals($user, $data['user']);
                $this->assertEquals($user->displayName, $data['username']);

                $this->assertStringContainsString('/email/confirm/', $data['url']);
                return true;
            });

        $confirm = $this->app->make(Confirmation::class);
        $this->assertTrue($confirm->sendEmailConfirmationLink($someUser));
    }

    public function testSendEmailConfirmationLinkNotAvailable(): void
    {
        $user = User::factory()->create();
        $this->config->set('email_confirmation', false);

        $confirm = $this->app->make(Confirmation::class);
        $this->assertFalse($confirm->sendEmailConfirmationLink($user));
    }

    public function testGetEmailConfirmationLink(): void
    {
        $user = User::factory()->create();

        $confirm = $this->app->make(Confirmation::class);
        $url = $confirm->getEmailConfirmationLink($user);

        $confirmLink = 'https://localhost/email/confirm/';
        $this->assertStringStartsWith($confirmLink, $url);
        $token = Str::replaceStart($confirmLink, '', $url);

        $data = $this->app->get(JWT::class)->decode($token);
        $this->assertEquals('email_address_confirmation', $data->sub);
        $this->assertObjectHasProperty('id', $data);
        $this->assertEquals($user->id, $data->id);
        $this->assertObjectHasProperty('address', $data);
        $this->assertEquals($user->email, $data->address);
    }

    public function testConfirmEmailAddress(): void
    {
        $user = User::factory()
            ->has(State::factory(['email_confirmed' => false]))
            ->create();
        $jwt = $this->app->make(JWT::class);

        $token = $jwt->encode([
            'sub' => 'email_address_confirmation',
            'id' => $user->id,
            'address' => $user->email,
        ]);

        $confirm = $this->app->make(Confirmation::class);
        $this->assertTrue($confirm->confirmEmailAddress($token));

        $user = User::find($user->id);
        $this->assertTrue($user->state->email_confirmed);
        $this->log->hasInfoThatContains('Confirmed email address for');
    }

    public function testConfirmEmailAddressNotFound(): void
    {
        $user = User::factory()
            ->has(State::factory(['email_confirmed' => false]))
            ->create();
        $jwt = $this->app->make(JWT::class);

        $token = $jwt->encode([
            'sub' => 'email_address_confirmation',
            'id' => $user->id,
            'address' => 'nope@localhorst',
        ]);

        $this->expectException(HttpNotFound::class);
        $confirm = $this->app->make(Confirmation::class);
        $confirm->confirmEmailAddress($token);
    }

    public function testConfirmEmailWrongSub(): void
    {
        $user = User::factory()
            ->has(State::factory(['email_confirmed' => false]))
            ->create();
        $jwt = $this->app->make(JWT::class);

        $token = $jwt->encode([
            'sub' => 'something-else',
            'id' => $user->id,
            'address' => $user->email,
        ]);

        $this->expectException(HttpNotFound::class);
        $confirm = $this->app->make(Confirmation::class);
        $confirm->confirmEmailAddress($token);
    }

    public function testConfirmEmailAddressInvalidJwt(): void
    {
        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjEzMzd9.2vUKrblndIXji4bnjvlTDU4S_pGw-iyM-1a0cQCKwOI';

        $confirm = $this->app->make(Confirmation::class);

        $this->expectException(HttpNotFound::class);
        $this->expectExceptionMessage('jwt.code_error');
        $confirm->confirmEmailAddress($jwt);
    }

    public function testConfirmEmailAddressNotAvailable(): void
    {
        $this->config->set('email_confirmation', false);

        $confirm = $this->app->make(Confirmation::class);
        $this->assertFalse($confirm->confirmEmailAddress(''));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->config = new Config([
            'url' => 'https://localhost',
            'app_key' => 'test42ButMakeItSecUr3!ANdLo000OooNg',
            'email_confirmation' => true,
            'jwt_algorithm' => 'HS256',
            'jwt_expiration_time' => 42,
        ]);
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->mailer = $this->createMock(EngelsystemMailer::class);
        $this->app->instance(EngelsystemMailer::class, $this->mailer);
    }
}
