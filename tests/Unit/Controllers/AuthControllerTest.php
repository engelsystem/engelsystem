<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\AuthController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversMethod(AuthController::class, '__construct')]
#[CoversMethod(AuthController::class, 'login')]
#[CoversMethod(AuthController::class, 'showLogin')]
#[CoversMethod(AuthController::class, 'postLogin')]
#[CoversMethod(AuthController::class, 'loginUser')]
#[CoversMethod(AuthController::class, 'logout')]
#[AllowMockObjectsWithoutExpectations]
class AuthControllerTest extends ControllerTestCase
{
    use HasDatabase;

    public function testLogin(): void
    {
        $response = $this->createMock(Response::class);
        list(, $session, $redirect, $config, $auth) = $this->getMocks();

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth);
        $controller->login();
    }

    public function testPostLogin(): void
    {
        $this->initDatabase();

        $request = new Request();
        $response = $this->createMock(Response::class);
        list(, , $redirect, $config, $auth) = $this->getMocks();
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);
        $validator = new Validator();
        $user = $this->createUser();

        $auth->expects($this->exactly(2))
            ->method('authenticate')
            ->with('foo', 'bar')
            ->willReturnOnConsecutiveCalls(null, $user);

        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data = []) use ($response) {
                $this->assertEquals('pages/login', $view);
                return $response;
            });

        $controller = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$response, $this->session, $redirect, $config, $auth])
            ->onlyMethods(['loginUser'])
            ->getMock();
        $controller->setValidator($validator);

        $controller->expects($this->once())
            ->method('loginUser')
            ->with($user)
            ->willReturn($response);

        // No credentials
        try {
            $controller->postLogin($request);
            $this->fail('Login without credentials possible');
        } catch (ValidationException) {
        }

        // Missing password
        $request = new Request([], ['login' => 'foo']);
        try {
            $controller->postLogin($request);
            $this->fail('Login without password possible');
        } catch (ValidationException) {
        }

        // No user found
        $request = new Request([], ['login' => 'foo', 'password' => 'bar']);
        $controller->postLogin($request);
        $this->assertHasNotification('auth.not-found', NotificationType::ERROR);

        // Authenticated user
        $controller->postLogin($request);
    }

    public function testLoginUser(): void
    {
        $this->initDatabase();

        $response = $this->createMock(Response::class);
        list(, , $redirect, $config, $auth) = $this->getMocks();
        $session = new Session(new MockArraySessionStorage());
        $session->set('foo', 'bar');
        $user = $this->createUser();

        $matcher = $this->exactly(2);
        $redirect->expects($matcher)
            ->method('to')->willReturnCallback(function (...$parameters) use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('news', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('/test', $parameters[0]);
                }
                return $response;
            });

        $controller = new AuthController($response, $session, $redirect, $config, $auth);
        $controller->loginUser($user);

        $this->assertFalse($session->has('foo'));
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals(['user_id' => 42, 'locale' => 'de_DE'], $session->all());

        // Redirect to previous page
        $session->set('previous_page', '/test');
        $controller->loginUser($user);
    }

    public function testLogout(): void
    {
        list($response, $session, $redirect, $config, $auth) = $this->getMocks();

        $session->expects($this->once())
            ->method('invalidate');

        $redirect->expects($this->once())
            ->method('to')
            ->with('/')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth);
        $return = $controller->logout();

        $this->assertEquals($response, $return);
    }

    protected function createUser(): User
    {
        return User::factory(['id' => 42])
            ->has(Settings::factory(['language' => 'de_DE']))
            ->create();
    }

    /**
     * @return array{Response, SessionInterface&MockObject, Redirector&MockObject, Authenticator&MockObject}
     */
    protected function getMocks(): array
    {
        $response = new Response();
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();
        $redirect = $this->createMock(Redirector::class);
        $config = new Config(['home_site' => 'news']);
        $auth = $this->createMock(Authenticator::class);

        $this->app->instance('session', $session);

        return [$response, $session, $redirect, $config, $auth];
    }
}
