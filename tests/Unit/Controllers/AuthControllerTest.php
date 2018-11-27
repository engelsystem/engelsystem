<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\AuthController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthControllerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\AuthController::__construct
     * @covers \Engelsystem\Controllers\AuthController::login
     */
    public function testLogin()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var SessionInterface|MockObject $session */
        /** @var UrlGeneratorInterface|MockObject $url */
        /** @var Authenticator|MockObject $auth */
        list(, $session, $url, $auth) = $this->getMocks();

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $url, $auth);
        $controller->login();
    }

    /**
     * @covers \Engelsystem\Controllers\AuthController::postLogin
     * @covers \Engelsystem\Controllers\AuthController::authenticateUser
     */
    public function testPostLogin()
    {
        $this->initDatabase();

        $request = new Request();
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var SessionInterface|MockObject $session */
        /** @var UrlGeneratorInterface|MockObject $url */
        /** @var Authenticator|MockObject $auth */
        list(, $session, $url, $auth) = $this->getMocks();

        $user = new User([
            'name'          => 'foo',
            'password'      => '',
            'email'         => '',
            'api_key'       => '',
            'last_login_at' => null,
        ]);
        $user->forceFill(['id' => 42,]);
        $user->save();

        $settings = new Settings(['language' => 'de_DE', 'theme' => '']);
        $settings->user()
            ->associate($user)
            ->save();

        $auth->expects($this->exactly(2))
            ->method('authenticate')
            ->with('foo', 'bar')
            ->willReturnOnConsecutiveCalls(null, $user);

        $response->expects($this->exactly(3))
            ->method('withView')
            ->withConsecutive(
                ['pages/login', ['errors' => ['auth.no-nickname'], 'show_password_recovery' => true]],
                ['pages/login', ['errors' => ['auth.no-password'], 'show_password_recovery' => true]],
                ['pages/login', ['errors' => ['auth.not-found'], 'show_password_recovery' => true]])
            ->willReturn($response);
        $response->expects($this->once())
            ->method('redirectTo')
            ->with('news')
            ->willReturn($response);

        $session->expects($this->once())
            ->method('invalidate');

        $session->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['user_id', 42],
                ['locale', 'de_DE']
            );

        $controller = new AuthController($response, $session, $url, $auth);
        $controller->postLogin($request);

        $request = new Request(['login' => 'foo']);
        $controller->postLogin($request);

        $request = new Request(['login' => 'foo', 'password' => 'bar']);
        // No user found
        $controller->postLogin($request);
        // Authenticated user
        $controller->postLogin($request);

        $this->assertNotNull($user->last_login_at);
    }

    /**
     * @covers \Engelsystem\Controllers\AuthController::logout
     */
    public function testLogout()
    {
        /** @var Response $response */
        /** @var SessionInterface|MockObject $session */
        /** @var UrlGeneratorInterface|MockObject $url */
        /** @var Authenticator|MockObject $auth */
        list($response, $session, $url, $auth) = $this->getMocks();

        $session->expects($this->once())
            ->method('invalidate');

        $url->expects($this->once())
            ->method('to')
            ->with('/')
            ->willReturn('https://foo.bar/');

        $controller = new AuthController($response, $session, $url, $auth);
        $return = $controller->logout();

        $this->assertEquals(['https://foo.bar/'], $return->getHeader('location'));
    }

    /**
     * @return array
     */
    protected function getMocks()
    {
        $response = new Response();
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);
        /** @var UrlGeneratorInterface|MockObject $url */
        $url = $this->getMockForAbstractClass(UrlGeneratorInterface::class);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);

        return [$response, $session, $url, $auth];
    }
}
