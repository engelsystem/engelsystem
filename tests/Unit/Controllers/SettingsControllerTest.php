<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\SettingsController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Psr\Log\LoggerInterface;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\Test\TestLogger;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\User\User;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Http\Exceptions\ValidationException;

class SettingsControllerTest extends TestCase
{
    use HasDatabase;

    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var Config */
    protected $config;

    /** @var TestLogger */
    protected $log;

    /** @var Response|MockObject */
    protected $response;

    /** @var Request */
    protected $request;

    /** @var User */
    protected $user;

    /**
     * @covers \Engelsystem\Controllers\SettingsController::password
     */
    public function testPassword()
    {
        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
        ->method('withView')
        ->willReturnCallback(function ($view, $data) {
            $this->assertEquals('pages/settings/password', $view);

            return $this->response;
        });

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->password();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePassword()
    {
        $body = [
            'password' => 'password',
            'new_password' => 'newpassword',
            'new_password2' => 'newpassword'
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'verifyPassword', [$this->user, 'password'], true, $this->once());
        $this->setExpects($this->auth, 'setPassword', [$this->user, 'newpassword'], null, $this->once());
        $this->setExpects(
            $this->response,
            'redirectTo',
            ['http://localhost/settings/password'],
            $this->response,
            $this->once()
        );

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->savePassword($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('User set new password.'));

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('settings.password.success', $messages[0]);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordWrongOldPassword()
    {
        $body = [
            'password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password2' => 'newpassword'
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'verifyPassword', [$this->user, 'wrongpassword'], false, $this->once());
        $this->setExpects($this->auth, 'setPassword', null, null, $this->never());
        $this->setExpects(
            $this->response,
            'redirectTo',
            ['http://localhost/settings/password'],
            $this->response,
            $this->once()
        );

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->savePassword($this->request);

        /** @var Session $session */
        $session = $this->app->get('session');
        $errors = $session->get('errors');
        $this->assertEquals('auth.password.error', $errors[0]);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordMismatchingNewPassword()
    {
        $body = [
            'password' => 'password',
            'new_password' => 'newpassword',
            'new_password2' => 'wrongpassword'
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'verifyPassword', [$this->user, 'password'], true, $this->once());
        $this->setExpects($this->auth, 'setPassword', null, null, $this->never());
        $this->setExpects(
            $this->response,
            'redirectTo',
            ['http://localhost/settings/password'],
            $this->response,
            $this->once()
        );

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->savePassword($this->request);

        /** @var Session $session */
        $session = $this->app->get('session');
        $errors = $session->get('errors');
        $this->assertEquals('validation.password.confirmed', $errors[0]);
    }

    /**
     * @return array
     */
    public function savePasswordValidationProvider(): array
    {
        return [
            [null, 'newpassword', 'newpassword'],
            ['password', null, 'newpassword'],
            ['password', 'newpassword', null],
            ['password', 'short', 'short']
        ];
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     * @dataProvider savePasswordValidationProvider
     * @param string $password
     * @param string $new_password
     * @param string $new_password2
     */
    public function testSavePasswordValidation(
        ?string $password,
        ?string $newPassword,
        ?string $newPassword2
    ) {
        $body = [
            'password' => $password,
            'new_password' => $newPassword,
            'new_password2' => $newPassword2
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'setPassword', null, null, $this->never());

        $this->expectException(ValidationException::class);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->savePassword($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::__construct
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauth()
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        config(['oauth' => $providers]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($providers) {
                $this->assertEquals('pages/settings/oauth', $view);
                $this->assertArrayHasKey('information', $data);
                $this->assertArrayHasKey('providers', $data);
                $this->assertEquals($providers, $data['providers']);

                return $this->response;
            });

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->oauth();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauthNotConfigured()
    {
        config(['oauth' => []]);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);

        $this->expectException(HttpNotFound::class);
        $controller->oauth();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithOAuth()
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        config(['oauth' => $providers]);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);

        $this->assertEquals([
            'http://localhost/user-settings' => 'settings.profile',
            'http://localhost/settings/password' => 'settings.password',
            'http://localhost/settings/oauth' => 'settings.oauth'
        ], $controller->settingsMenu());
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithoutOAuth()
    {
        config(['oauth' => []]);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);

        $this->assertEquals([
            'http://localhost/user-settings' => 'settings.profile',
            'http://localhost/settings/password' => 'settings.password'
        ], $controller->settingsMenu());
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->config = new Config(['min_password_length' => 6]);
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);

        $this->request = Request::create('http://localhost');
        $this->app->instance('request', $this->request);
        $this->app->instance(Request::class, $this->request);
        $this->app->instance(ServerRequestInterface::class, $this->request);

        $this->response = $this->createMock(Response::class);
        $this->app->instance(Response::class, $this->response);

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);
        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->app->instance('session', new Session(new MockArraySessionStorage()));

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->user = User::factory()->create();
    }
}
