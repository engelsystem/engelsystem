<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\RegistrationController;
use Engelsystem\Database\Database;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class RegistrationControllerTest extends TestCase
{
    use HasDatabase;

    protected $config = [
        'enable_pending_registrations'  => false,
        'registration_enabled'          => true,
        'enable_tshirt_size'            => false,
        'enable_planned_arrival'        => false,
        'min_password_length'           => 8,
        'enable_dect'                   => false,
        'enable_user_name'              => false,
        'theme'                         => 0,
        'autoarrive'                    => false,
    ];

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::register
     */
    public function testRegister(): void
    {
        $this->initDatabase();

        list(, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config([]),
            $translator,
            $this->database
        );

        $response = $controller->register();

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::postRegister
     */
    public function testPostRegister(): void
    {
        $this->initDatabase();
        $database = $this->createMock(Database::class);
        $database->method('select')->willReturn([]);

        list(, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config($this->config),
            $translator,
            $database
        );
        $controller->setValidator(new Validator());

        $request = new Request([], [
            'login'                 => 'testengel-123',
            'email'                 => 'mail@example.com',
            'password'              => 'asdfasdf',
            'password_confirmation' => 'asdfasdf',
        ]);

        $response = $controller->postRegister($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(['/'], $response->getHeader('location'));
        $this->assertNotEmpty(User::whereEmail('mail@example.com')->first());
        $this->assertTrue($log->hasInfoThatContains('testengel-123'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::postRegister
     */
    public function testPostRegisterDisabled(): void
    {
        $this->initDatabase();

        list(, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config(array_merge($this->config, ['registration_enabled' => false])),
            $translator,
            $this->database
        );

        $response = $controller->postRegister(new Request([]));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(['/'], $response->getHeader('location'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::postRegister
     */
    public function testPostRegisterInvalidRequest(): void
    {
        $this->initDatabase();

        list(, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config($this->config),
            $translator,
            $this->database
        );
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->postRegister(new Request());
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::postRegister
     */
    public function testPostRegisterExists(): void
    {
        $this->initDatabase();

        $user = new User([
            'name'          => 'testengel-123',
            'password'      => '',
            'email'         => 'mail@example.com',
            'api_key'       => '',
            'last_login_at' => null,
        ]);
        $user->save();

        $this->assertNotEmpty(User::whereEmail('mail@example.com')->first());

        list($renderer, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config($this->config),
            $translator,
            $this->database
        );
        $controller->setValidator(new Validator());

        $this->setExpects($renderer, 'render', [
            'pages/register',
            ['errors' => collect(['registration.exists.email'])]
        ]);

        $controller->postRegister(new Request([], [
            'login'                 => 'testengel-42',
            'email'                 => 'mail@example.com',
            'password'              => 'asdfasdf',
            'password_confirmation' => 'asdfasdf',
        ]));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::postRegister
     */
    public function testPostRegisterPending(): void
    {
        $this->initDatabase();
        $database = $this->createMock(Database::class);
        $database->method('select')->willReturn([]);

        list(, $response, $session, $auth, $mail, $log, $translator) = $this->getMocks();
        $auth->method('user')->willReturn('notnull');
        $controller = new RegistrationController(
            $response,
            $session,
            $auth,
            $mail,
            $log,
            new Config(array_merge($this->config, ['enable_pending_registrations'  => true])),
            $translator,
            $database
        );
        $controller->setValidator(new Validator());

        $request = new Request([], [
            'login' => 'testengel-123',
            'email' => 'mail@example.com',
        ]);

        $this->setExpects($mail, 'sendViewTranslated');

        $controller->postRegister($request);

        $this->assertNotEmpty(User::whereEmail('mail@example.com')->first());
        $this->assertTrue($log->hasInfoThatContains('testengel-123'));
    }

    /**
     * @return array
     */
    protected function getMocks(): array
    {
        $renderer = $this->createMock(Renderer::class);
        $response = new Response();
        $response->setRenderer($renderer);
        $session = new Session(new MockArraySessionStorage());
        $session->set('locale', 'fo_OO');
        $auth = $this->createMock(Authenticator::class);
        $mail = $this->createMock(EngelsystemMailer::class);
        $log = new TestLogger();
        $translator = $this->createMock(Translator::class);
        $translator->method('translate')->willReturn('');

        return [$renderer, $response, $session, $auth, $mail, $log, $translator];
    }
}
