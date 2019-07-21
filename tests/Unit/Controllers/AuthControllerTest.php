<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\AuthController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class AuthControllerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\AuthController::__construct
     * @covers \Engelsystem\Controllers\AuthController::login
     * @covers \Engelsystem\Controllers\AuthController::showLogin
     */
    public function testLogin()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var SessionInterface|MockObject $session */
        /** @var UrlGeneratorInterface|MockObject $url */
        /** @var Authenticator|MockObject $auth */
        list(, $session, $url, $auth) = $this->getMocks();

        $session->expects($this->once())
            ->method('get')
            ->with('errors', [])
            ->willReturn(['foo' => 'bar']);
        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login')
            ->willReturn($response);

        $controller = new AuthController($response, $session, $url, $auth);
        $controller->login();
    }

    /**
     * @covers \Engelsystem\Controllers\AuthController::postLogin
     */
    public function testPostLogin()
    {
        $this->initDatabase();

        $request = new Request();
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var UrlGeneratorInterface|MockObject $url */
        /** @var Authenticator|MockObject $auth */
        list(, , $url, $auth) = $this->getMocks();
        $session = new Session(new MockArraySessionStorage());
        /** @var Validator|MockObject $validator */
        $validator = new Validator();

        $user = new User([
            'name'          => 'foo',
            'password'      => '',
            'email'         => '',
            'api_key'       => '',
            'last_login_at' => null,
        ]);
        $user->forceFill(['id' => 42]);
        $user->save();

        $settings = new Settings(['language' => 'de_DE', 'theme' => '']);
        $settings->user()
            ->associate($user)
            ->save();

        $auth->expects($this->exactly(2))
            ->method('authenticate')
            ->with('foo', 'bar')
            ->willReturnOnConsecutiveCalls(null, $user);

        $response->expects($this->once())
            ->method('withView')
            ->with('pages/login', ['errors' => Collection::make(['auth.not-found'])])
            ->willReturn($response);
        $response->expects($this->once())
            ->method('redirectTo')
            ->with('news')
            ->willReturn($response);

        // No credentials
        $controller = new AuthController($response, $session, $url, $auth);
        $controller->setValidator($validator);
        try {
            $controller->postLogin($request);
            $this->fail('Login without credentials possible');
        } catch (ValidationException $e) {
        }

        // Missing password
        $request = new Request([], ['login' => 'foo']);
        try {
            $controller->postLogin($request);
            $this->fail('Login without password possible');
        } catch (ValidationException $e) {
        }

        // No user found
        $request = new Request([], ['login' => 'foo', 'password' => 'bar']);
        $controller->postLogin($request);
        $this->assertEquals([], $session->all());

        // Authenticated user
        $controller->postLogin($request);

        $this->assertNotNull($user->last_login_at);
        $this->assertEquals(['user_id' => 42, 'locale' => 'de_DE'], $session->all());
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
