<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
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
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class AuthControllerTest extends ControllerTest
{
    use ArraySubsetAsserts;
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\AuthController::__construct
     * @covers \Engelsystem\Controllers\AuthController::login
     * @covers \Engelsystem\Controllers\AuthController::showLogin
     */
    public function testLogin(): void
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var SessionInterface|MockObject $session */
        /** @var Redirector|MockObject $redirect */
        /** @var Config $config */
        /** @var Authenticator|MockObject $auth */
        list(, $session, $redirect, $config, $auth) = $this->getMocks();

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth);
        $controller->login();
    }

    /**
     * @covers \Engelsystem\Controllers\AuthController::postLogin
     */
    public function testPostLogin(): void
    {
        $this->initDatabase();

        $request = new Request();
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var Redirector|MockObject $redirect */
        /** @var Config $config */
        /** @var Authenticator|MockObject $auth */
        list(, , $redirect, $config, $auth) = $this->getMocks();
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);
        /** @var Validator|MockObject $validator */
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

        /** @var AuthController|MockObject $controller */
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

    /**
     * @covers \Engelsystem\Controllers\AuthController::loginUser
     */
    public function testLoginUser(): void
    {
        $this->initDatabase();

        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var Redirector|MockObject $redirect */
        /** @var Config $config */
        /** @var Authenticator|MockObject $auth */
        list(, , $redirect, $config, $auth) = $this->getMocks();
        $session = new Session(new MockArraySessionStorage());
        $session->set('foo', 'bar');
        $user = $this->createUser();

        $redirect->expects($this->exactly(2))
            ->method('to')
            ->withConsecutive(['news'], ['/test'])
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth);
        $controller->loginUser($user);

        $this->assertFalse($session->has('foo'));
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals(['user_id' => 42, 'locale' => 'de_DE'], $session->all());

        // Redirect to previous page
        $session->set('previous_page', '/test');
        $controller->loginUser($user);
    }

    /**
     * @covers \Engelsystem\Controllers\AuthController::logout
     */
    public function testLogout(): void
    {
        /** @var Response $response */
        /** @var SessionInterface|MockObject $session */
        /** @var Redirector|MockObject $redirect */
        /** @var Config $config */
        /** @var Authenticator|MockObject $auth */
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

    protected function getMocks(): array
    {
        $response = new Response();
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);
        /** @var Redirector|MockObject $redirect */
        $redirect = $this->createMock(Redirector::class);
        $config = new Config(['home_site' => 'news']);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        $this->app->instance('session', $session);

        return [$response, $session, $redirect, $config, $auth];
    }
}
