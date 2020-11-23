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
use Engelsystem\Http\Redirector;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\Test\TestLogger;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\User\User;

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
            $this->assertEquals('pages/settings/password.twig', $view);

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

        $this->auth->expects($this->once())
        ->method('user')
        ->willReturn($this->user);

        $this->auth->expects($this->once())
        ->method('verifyPassword')
        ->with($this->user, 'password')
        ->willReturn(true);

        $this->auth->expects($this->once())
        ->method('setPassword')
        ->with($this->user, 'newpassword');

        $this->response->expects($this->once())
        ->method('redirectTo')
        ->with('http://localhost/settings/password')
        ->willReturn($this->response);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->savePassword($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('User set new password.'));

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('Password saved.', $messages[0]);
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

        $this->auth->expects($this->once())
        ->method('user')
        ->willReturn($this->user);

        $this->auth->expects($this->once())
        ->method('verifyPassword')
        ->with($this->user, 'wrongpassword')
        ->willReturn(false);

        $this->auth->expects($this->never())
        ->method('setPassword');

        $this->response->expects($this->once())
        ->method('redirectTo')
        ->with('http://localhost/settings/password')
        ->willReturn($this->response);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->savePassword($this->request);

        /** @var Session $session */
        $session = $this->app->get('session');
        $errors = $session->get('errors');
        $this->assertEquals('-> not OK. Please try again.', $errors[0]);
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

        $this->auth->expects($this->once())
        ->method('user')
        ->willReturn($this->user);

        $this->auth->expects($this->once())
        ->method('verifyPassword')
        ->with($this->user, 'password')
        ->willReturn(true);

        $this->auth->expects($this->never())
        ->method('setPassword');

        $this->response->expects($this->once())
        ->method('redirectTo')
        ->with('http://localhost/settings/password')
        ->willReturn($this->response);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->savePassword($this->request);

        /** @var Session $session */
        $session = $this->app->get('session');
        $errors = $session->get('errors');
        $this->assertEquals('Your passwords don\'t match.', $errors[0]);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordInvalidNewPassword()
    {
        $body = [
            'password' => 'password',
            'new_password' => 'short',
            'new_password2' => 'short'
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->auth->expects($this->once())
        ->method('user')
        ->willReturn($this->user);

        $this->auth->expects($this->once())
        ->method('verifyPassword')
        ->with($this->user, 'password')
        ->willReturn(true);

        $this->auth->expects($this->never())
        ->method('setPassword');

        $this->response->expects($this->once())
        ->method('redirectTo')
        ->with('http://localhost/settings/password')
        ->willReturn($this->response);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->savePassword($this->request);

        /** @var Session $session */
        $session = $this->app->get('session');
        $errors = $session->get('errors');
        $this->assertEquals('Your password is to short (please use at least 6 characters).', $errors[0]);
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
                $this->assertEquals('pages/settings/oauth.twig', $view);
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

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->app->instance('session', new Session(new MockArraySessionStorage()));

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->user = new User([
            'name'      => 'testuser',
            'email' => 'test@engelsystem.de',
            'password' => 'xxx',
            'api_key' => 'xxx'
        ]);
        $this->user->save();
    }
}
