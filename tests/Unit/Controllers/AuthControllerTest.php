<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\AuthController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Confirmation;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
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
#[CoversMethod(AuthController::class, 'confirmEmail')]
#[AllowMockObjectsWithoutExpectations]
class AuthControllerTest extends ControllerTestCase
{
    use HasDatabase;

    public function testLogin(): void
    {
        $response = $this->createMock(Response::class);
        list(, $session, $redirect, $config, $auth, $confirm) = $this->getMocks();

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth, $confirm);
        $controller->login();
    }

    public function testPostLogin(): void
    {
        $this->initDatabase();

        $request = new Request();
        $response = $this->createMock(Response::class);
        list(, , $redirect, $config, $auth, $confirm) = $this->getMocks();
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);
        $validator = new Validator();
        $user = $this->createUser();

        $auth->expects($this->exactly(4))
            ->method('authenticate')
            ->with('foo', 'bar')
            ->willReturnOnConsecutiveCalls(null, $user, $user, $user);

        $confirm->expects($this->exactly(3))
            ->method('confirmationAvailable')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $confirm->expects($this->exactly(2))
            ->method('sendEmailConfirmationLink')
            ->with($user)
            ->willReturnOnConsecutiveCalls(true, false);

        $response->expects($this->exactly(3))
            ->method('withView')
            ->willReturnCallback(function ($view, $data = []) use ($response) {
                $this->assertEquals('pages/login', $view);
                return $response;
            });

        $controller = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$response, $this->session, $redirect, $config, $auth, $confirm])
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

        // Needs confirmation
        $controller->postLogin($request);
        $this->assertHasNotification('auth.email-confirmation-request', NotificationType::WARNING);

        // Not able to confirm
        $controller->postLogin($request);
        $this->assertHasNotification('auth.unable-to-confirm', NotificationType::ERROR);

        // Authenticated user
        $controller->postLogin($request);
    }

    public function testLoginUser(): void
    {
        $this->initDatabase();

        $response = $this->createMock(Response::class);
        list(, , $redirect, $config, $auth, $confirm) = $this->getMocks();
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

        $controller = new AuthController($response, $session, $redirect, $config, $auth, $confirm);
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
        list($response, $session, $redirect, $config, $auth, $confirm) = $this->getMocks();

        $session->expects($this->once())
            ->method('invalidate');

        $redirect->expects($this->once())
            ->method('to')
            ->with('/')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $redirect, $config, $auth, $confirm);
        $return = $controller->logout();

        $this->assertEquals($response, $return);
    }

    public function testConfirmEmail(): void
    {
        $token = '5oMES3cure.t0keN.tes1';
        $request = new Request([], [], ['token' => $token]);
        list($response, , $redirect, $config, $auth, $confirm) = $this->getMocks();
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);

        $this->setExpects($redirect, 'to', ['/'], $response);
        $this->setExpects($confirm, 'confirmationAvailable', null, true);
        $this->setExpects($confirm, 'confirmEmailAddress', [$token], true);

        $controller = new AuthController($response, $this->session, $redirect, $config, $auth, $confirm);

        $return = $controller->confirmEmail($request);
        $this->assertEquals($response, $return);
        $this->assertHasNotification('auth.confirmed', NotificationType::INFORMATION);
    }

    public function testConfirmEmailProblem(): void
    {
        $token = 'An0tHersEcuR3.t0keN.tes1';
        $request = new Request([], [], ['token' => $token]);
        list($response, , $redirect, $config, $auth, $confirm) = $this->getMocks();
        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);

        $this->setExpects($redirect, 'to', ['/'], $response);
        $this->setExpects($confirm, 'confirmationAvailable', null, true);
        $this->setExpects($confirm, 'confirmEmailAddress', [$token], false);

        $controller = new AuthController($response, $this->session, $redirect, $config, $auth, $confirm);

        $return = $controller->confirmEmail($request);
        $this->assertEquals($response, $return);
        $this->assertHasNotification('auth.unable-to-confirm', NotificationType::ERROR);
    }

    public function testConfirmEmailNotAvailable(): void
    {
        list($response, $session, $redirect, $config, $auth, $confirm) = $this->getMocks();
        $request = new Request();

        $controller = new AuthController($response, $session, $redirect, $config, $auth, $confirm);

        $this->expectException(HttpNotFound::class);
        $controller->confirmEmail($request);
    }

    protected function createUser(): User
    {
        return User::factory(['id' => 42])
            ->has(Settings::factory(['language' => 'de_DE']))
            ->has(State::factory(['email_confirmed' => false]))
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
        $confirm = $this->createMock(Confirmation::class);
        $config = new Config(['home_site' => 'news']);
        $auth = $this->createMock(Authenticator::class);

        $this->app->instance('session', $session);

        return [$response, $session, $redirect, $config, $auth, $confirm];
    }
}
