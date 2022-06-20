<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\SettingsController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\Settings;
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

    /** @var Session */
    protected $session;

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
    public function testSavePasswordWhenEmpty()
    {
        $this->user->password = '';
        $this->user->save();

        $body = [
            'new_password'  => 'anotherpassword',
            'new_password2' => 'anotherpassword'
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'setPassword', [$this->user, 'anotherpassword'], null, $this->once());
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
     * @testdox theme: underNormalConditions -> returnsCorrectViewAndData
     * @covers \Engelsystem\Controllers\SettingsController::theme
     */
    public function testThemeUnderNormalConditionReturnsCorrectViewAndData()
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());

        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/theme', $view);
                $this->assertArrayHasKey('settings_menu', $data);
                $this->assertArrayHasKey('themes', $data);
                $this->assertArrayHasKey('current_theme', $data);
                $this->assertEquals([0 => 'Engelsystem light', 1 => 'Engelsystem dark'], $data['themes']);
                $this->assertEquals(1, $data['current_theme']);

                return $this->response;
            });

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->theme();
    }

    /**
     * @testdox saveTheme: withNoSelectedThemeGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithNoSelectedThemeGivenThrowsException()
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveTheme($this->request);
    }

    /**
     * @testdox saveTheme: withUnknownSelectedThemeGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithUnknownSelectedThemeGivenThrowsException()
    {
        $this->request = $this->request->withParsedBody(['select_theme' => 2]);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(HttpNotFound::class);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveTheme($this->request);
    }

    /**
     * @testdox saveTheme: withKnownSelectedThemeGiven -> savesThemeAndRedirect
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithKnownSelectedThemeGivenSavesThemeAndRedirect()
    {
        $this->assertEquals(1, $this->user->settings->theme);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/theme')
            ->willReturn($this->response);

        $this->request = $this->request->withParsedBody(['select_theme' => 0]);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveTheme($this->request);

        $this->assertEquals(0, $this->user->settings->theme);
    }

    /**
     * @testdox language: underNormalConditions -> returnsCorrectViewAndData
     * @covers \Engelsystem\Controllers\SettingsController::language
     */
    public function testLanguageUnderNormalConditionReturnsCorrectViewAndData()
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());

        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/language', $view);
                $this->assertArrayHasKey('settings_menu', $data);
                $this->assertArrayHasKey('languages', $data);
                $this->assertArrayHasKey('current_language', $data);
                $this->assertEquals(['en_US' => 'English', 'de_DE' => 'Deutsch'], $data['languages']);
                $this->assertEquals('en_US', $data['current_language']);

                return $this->response;
            });

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->language();
    }

    /**
     * @testdox saveLanguage: withNoSelectedLanguageGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithNoSelectedLanguageGivenThrowsException()
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveLanguage($this->request);
    }

    /**
     * @testdox saveLanguage: withUnknownSelectedLanguageGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithUnknownSelectedLanguageGivenThrowsException()
    {
        $this->request = $this->request->withParsedBody(['select_language' => 'unknown']);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(HttpNotFound::class);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveLanguage($this->request);
    }

    /**
     * @testdox saveLanguage: withKnownSelectedLanguageGiven -> savesLanguageAndUpdatesSessionAndRedirect
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithKnownSelectedLanguageGivenSavesLanguageAndUpdatesSessionAndRedirect()
    {
        $this->assertEquals('en_US', $this->user->settings->language);
        $this->session->set('locale', 'en_US');

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/language')
            ->willReturn($this->response);

        $this->request = $this->request->withParsedBody(['select_language' => 'de_DE']);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);
        $controller->setValidator(new Validator());
        $controller->saveLanguage($this->request);

        $this->assertEquals('de_DE', $this->user->settings->language);
        $this->assertEquals('de_DE', $this->session->get('locale'));
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
     * @covers \Engelsystem\Controllers\SettingsController::checkOauthHidden
     */
    public function testSettingsMenuWithOAuth()
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        $providersHidden = ['foo' => ['lorem' => 'ipsum', 'hidden' => true]];
        config(['oauth' => $providers]);

        /** @var SettingsController $controller */
        $controller = $this->app->make(SettingsController::class);

        $this->assertEquals([
            'http://localhost/user-settings' => 'settings.profile',
            'http://localhost/settings/password' => 'settings.password',
            'http://localhost/settings/language' => 'settings.language',
            'http://localhost/settings/theme' => 'settings.theme',
            'http://localhost/settings/oauth' => ['title' => 'settings.oauth', 'hidden' => false]
        ], $controller->settingsMenu());

        config(['oauth' => $providersHidden]);
        $this->assertEquals([
            'http://localhost/user-settings' => 'settings.profile',
            'http://localhost/settings/password' => 'settings.password',
            'http://localhost/settings/language' => 'settings.language',
            'http://localhost/settings/theme' => 'settings.theme',
            'http://localhost/settings/oauth' => ['title' => 'settings.oauth', 'hidden' => true]
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
            'http://localhost/settings/password' => 'settings.password',
            'http://localhost/settings/language' => 'settings.language',
            'http://localhost/settings/theme' => 'settings.theme'
        ], $controller->settingsMenu());
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $themes = [
            0 => ['name' => 'Engelsystem light'],
            1 => ['name' => 'Engelsystem dark']
        ];
        $languages = [
            'en_US' => 'English',
            'de_DE' => 'Deutsch'
        ];
        $this->config = new Config(['min_password_length' => 6, 'themes' => $themes, 'locales' => $languages]);
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

        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->user = User::factory()
            ->has(Settings::factory(['theme' => 1, 'language' => 'en_US']))
            ->create();
    }
}
