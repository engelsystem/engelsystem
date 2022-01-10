<?php

namespace Engelsystem\Test\Unit\Controllers;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Config\Config;
use Engelsystem\Controllers\PasswordResetController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PasswordResetControllerTest extends TestCase
{
    use ArraySubsetAsserts;
    use HasDatabase;

    /** @var array */
    protected $args = [];

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::reset
     * @covers \Engelsystem\Controllers\PasswordResetController::__construct
     */
    public function testReset(): void
    {
        $controller = $this->getController('pages/password/reset');
        $response = $controller->reset();

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::postReset
     */
    public function testPostReset(): void
    {
        $this->initDatabase();
        $request = new Request([], ['email' => 'foo@bar.batz']);
        $user = $this->createUser();

        $controller = $this->getController(
            'pages/password/reset-success',
            ['type' => 'email', 'errors' => collect()]
        );
        /** @var TestLogger $log */
        $log = $this->args['log'];
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->args['mailer'];
        $this->setExpects($mailer, 'sendViewTranslated');

        $controller->postReset($request);

        $this->assertNotEmpty((new PasswordReset())->find($user->id)->first());
        $this->assertTrue($log->hasInfoThatContains($user->name));
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::postReset
     */
    public function testPostResetInvalidRequest(): void
    {
        $request = new Request();

        $controller = $this->getController();

        $this->expectException(ValidationException::class);
        $controller->postReset($request);
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::postReset
     */
    public function testPostResetNoUser(): void
    {
        $this->initDatabase();
        $request = new Request([], ['email' => 'foo@bar.batz']);

        $controller = $this->getController(
            'pages/password/reset-success',
            ['type' => 'email', 'errors' => collect()]
        );

        $controller->postReset($request);
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::resetPassword
     * @covers \Engelsystem\Controllers\PasswordResetController::requireToken
     */
    public function testResetPassword(): void
    {
        $this->initDatabase();

        $this->app->instance('config', new Config(['min_password_length' => 3]));
        $user = $this->createUser();
        $token = $this->createToken($user);
        $request = new Request([], [], ['token' => $token->token]);

        $controller = $this->getController('pages/password/reset-form');

        $controller->resetPassword($request);
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::resetPassword
     * @covers \Engelsystem\Controllers\PasswordResetController::requireToken
     */
    public function testResetPasswordNoToken(): void
    {
        $this->initDatabase();
        $controller = $this->getController();

        $this->expectException(HttpNotFound::class);
        $controller->resetPassword(new Request());
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::postResetPassword
     */
    public function testPostResetPassword(): void
    {
        $this->initDatabase();

        $this->app->instance('config', new Config(['min_password_length' => 3]));
        $user = $this->createUser();
        $token = $this->createToken($user);
        $password = 'SomeRandomPasswordForAmazingSecurity';
        $request = new Request(
            [],
            ['password' => $password, 'password_confirmation' => $password],
            ['token' => $token->token]
        );

        $controller = $this->getController(
            'pages/password/reset-success',
            ['type' => 'reset', 'errors' => collect()]
        );

        $auth = new Authenticator($request, $this->args['session'], $user);
        $this->app->instance('authenticator', $auth);

        $response = $controller->postResetPassword($request);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEmpty((new PasswordReset())->find($user->id));
        $this->assertNotNull(auth()->authenticate($user->name, $password));
    }

    /**
     * @covers \Engelsystem\Controllers\PasswordResetController::postResetPassword
     * @covers \Engelsystem\Controllers\PasswordResetController::showView
     */
    public function testPostResetPasswordNotMatching(): void
    {
        $this->initDatabase();

        $this->app->instance('config', new Config(['min_password_length' => 3]));
        $user = $this->createUser();
        $token = $this->createToken($user);
        $password = 'SomeRandomPasswordForAmazingSecurity';
        $request = new Request(
            [],
            ['password' => $password, 'password_confirmation' => $password . 'OrNot'],
            ['token' => $token->token]
        );

        $controller = $this->getController(
            'pages/password/reset-form',
            ['errors' => collect(['some.other.error', 'validation.password.confirmed'])]
        );
        /** @var Session $session */
        $session = $this->args['session'];
        $session->set('errors', ['foo' => ['bar' => 'some.other.error']]);

        $controller->postResetPassword($request);
        $this->assertEmpty($session->get('errors'));
    }

    /**
     * @return array
     */
    protected function getControllerArgs(): array
    {
        $response = new Response();
        $session = new Session(new MockArraySessionStorage());
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->createMock(EngelsystemMailer::class);
        $log = new TestLogger();
        $renderer = $this->createMock(Renderer::class);
        $response->setRenderer($renderer);

        $this->app->instance('session', $session);

        return $this->args = [
            'response' => $response,
            'session'  => $session,
            'mailer'   => $mailer,
            'log'      => $log,
            'renderer' => $renderer
        ];
    }

    /**
     * @param string $view
     * @param array  $data
     * @return PasswordResetController
     */
    protected function getController(?string $view = null, ?array $data = null): PasswordResetController
    {
        /** @var Response $response */
        /** @var Session $session */
        /** @var EngelsystemMailer|MockObject $mailer */
        /** @var TestLogger $log */
        /** @var Renderer|MockObject $renderer */
        list($response, $session, $mailer, $log, $renderer) = array_values($this->getControllerArgs());
        $controller = new PasswordResetController($response, $session, $mailer, $log);
        $controller->setValidator(new Validator());

        if ($view) {
            /** @var array|mixed[] $args */
            $args = [$view];
            if ($data) {
                $args[] = $data;
            }

            $renderer->expects($this->atLeastOnce())
                ->method('render')
                ->willReturnCallback(function ($template, $data = []) use ($args) {
                    $this->assertEquals($args[0], $template);
                    if (isset($args[1])) {
                        $this->assertArraySubset($args[1], $data);
                    }

                    return 'Foo';
                });
        }

        return $controller;
    }

    /**
     * @return User
     */
    protected function createUser(): User
    {
        return User::factory()->create(['email' => 'foo@bar.batz']);
    }

    /**
     * @param User $user
     * @return PasswordReset
     */
    protected function createToken(User $user): PasswordReset
    {
        $reset = new PasswordReset(['user_id' => $user->id, 'token' => 'SomeTestToken123']);
        $reset->save();

        return $reset;
    }
}
