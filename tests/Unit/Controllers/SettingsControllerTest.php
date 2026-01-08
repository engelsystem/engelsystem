<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Controllers\SettingsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\Session as SessionModel;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserSupervisorStatus;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class SettingsControllerTest extends ControllerTest
{
    use HasDatabase;

    protected Authenticator|MockObject $auth;

    protected User $user;

    protected SettingsController $controller;

    protected SessionModel $currentSession;
    protected SessionModel $secondSession;
    protected SessionModel $otherSession;

    protected function setUpProfileTest(): array
    {
        $body = [
            'pronoun'                => 'Herr',
            'first_name'             => 'John',
            'last_name'              => 'Doe',
            'planned_arrival_date'   => '2022-01-01',
            'planned_departure_date' => '2022-01-02',
            'dect'                   => '1234',
            'mobile'                 => '0123456789',
            'mobile_show'            => true,
            'email'                  => 'a@bc.de',
            'email_shiftinfo'        => true,
            'email_news'             => true,
            'email_human'            => true,
            'email_messages'         => true,
            'email_goodie'            => true,
            'shirt_size'             => 'S',
        ];
        $this->request = $this->request->withParsedBody($body);
        $this->setExpects(
            $this->response,
            'redirectTo',
            ['http://localhost/settings/profile'],
            $this->response,
            $this->atLeastOnce()
        );

        config([
            'enable_pronoun'         => true,
            'enable_full_name'       => true,
            'enable_planned_arrival' => true,
            'enable_dect'            => true,
            'enable_mobile_show'     => true,
            'enable_email_goodie'     => true,
            'goodie_type'            => GoodieType::Tshirt->value,
        ]);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->setValidator(new Validator());

        return $body;
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::profile
     */
    public function testProfile(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());
        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/profile', $view);
                $this->assertArrayHasKey('userdata', $data);
                $this->assertEquals($this->user, $data['userdata']);
                $this->assertArrayHasKey('isMinor', $data);
                $this->assertArrayHasKey('willingToSupervise', $data);
                $this->assertFalse($data['isMinor']);
                $this->assertFalse($data['willingToSupervise']);
                return $this->response;
            });

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->profile();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::profile
     */
    public function testProfileForMinor(): void
    {
        // Make the user a minor
        $minorCategory = MinorCategory::factory()->create();
        $this->user->minor_category_id = $minorCategory->id;
        $this->user->save();

        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());
        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/profile', $view);
                $this->assertArrayHasKey('isMinor', $data);
                $this->assertTrue($data['isMinor']);
                return $this->response;
            });

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->profile();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::profile
     */
    public function testProfileWithExistingSupervisorStatus(): void
    {
        // Create supervisor status with willing_to_supervise = true
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $this->user->id]);
        $this->user->refresh();

        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());
        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertArrayHasKey('willingToSupervise', $data);
                $this->assertTrue($data['willingToSupervise']);
                return $this->response;
            });

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->profile();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     * @covers \Engelsystem\Controllers\SettingsController::getSaveProfileRules
     * @covers \Engelsystem\Controllers\SettingsController::isRequired
     */
    public function testSaveProfile(): void
    {
        $body = $this->setUpProfileTest();
        $this->controller->saveProfile($this->request);

        $this->assertEquals($body['pronoun'], $this->user->personalData->pronoun);
        $this->assertEquals($body['first_name'], $this->user->personalData->first_name);
        $this->assertEquals($body['last_name'], $this->user->personalData->last_name);
        $this->assertEquals(
            $body['planned_arrival_date'],
            $this->user->personalData->planned_arrival_date->format('Y-m-d')
        );
        $this->assertEquals(
            $body['planned_departure_date'],
            $this->user->personalData->planned_departure_date->format('Y-m-d')
        );
        $this->assertEquals($body['dect'], $this->user->contact->dect);
        $this->assertEquals($body['mobile'], $this->user->contact->mobile);
        $this->assertEquals($body['mobile_show'], $this->user->settings->mobile_show);
        $this->assertEquals($body['email'], $this->user->email);
        $this->assertEquals($body['email_shiftinfo'], $this->user->settings->email_shiftinfo);
        $this->assertEquals($body['email_news'], $this->user->settings->email_news);
        $this->assertEquals($body['email_human'], $this->user->settings->email_human);
        $this->assertEquals($body['email_messages'], $this->user->settings->email_messages);
        $this->assertEquals($body['email_goodie'], $this->user->settings->email_goodie);
        $this->assertEquals($body['shirt_size'], $this->user->personalData->shirt_size);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileThrowsErrorOnInvalidArrival(): void
    {
        $this->setUpProfileTest();
        config(['buildup_start' => new Carbon('2022-01-02')]); // arrival before buildup
        $this->controller->saveProfile($this->request);
        $this->assertHasNotification('settings.profile.planned_arrival_date.invalid', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileThrowsErrorOnInvalidDeparture(): void
    {
        $this->setUpProfileTest();
        config(['teardown_end' => new Carbon('2022-01-01')]); // departure after teardown
        $this->controller->saveProfile($this->request);
        $this->assertHasNotification('settings.profile.planned_departure_date.invalid', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresPronounIfDisabled(): void
    {
        $this->setUpProfileTest();
        config(['enable_pronoun' => false]);
        $this->controller->saveProfile($this->request);
        $this->assertEquals('', $this->user->personalData->pronoun);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresFirstAndLastnameIfDisabled(): void
    {
        $this->setUpProfileTest();
        config(['enable_full_name' => false]);
        $this->controller->saveProfile($this->request);
        $this->assertEquals('', $this->user->personalData->first_name);
        $this->assertEquals('', $this->user->personalData->last_name);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresArrivalDatesIfDisabled(): void
    {
        $this->setUpProfileTest();
        config(['enable_planned_arrival' => false]);
        $this->controller->saveProfile($this->request);
        $this->assertEquals('', $this->user->personalData->planned_arrival_date);
        $this->assertEquals('', $this->user->personalData->planned_departure_date);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresDectIfDisabled(): void
    {
        $this->setUpProfileTest();
        config(['enable_dect' => false]);
        $this->controller->saveProfile($this->request);
        $this->assertEquals('', $this->user->contact->dect);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresMobileShowIfDisabled(): void
    {
        $this->setUpProfileTest();
        config(['enable_mobile_show' => false]);
        $this->controller->saveProfile($this->request);
        $this->assertFalse($this->user->settings->mobile_show);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresEmailGoodieIfDisabled(): void
    {
        $this->setUpProfileTest();
        $this->config->set('goodie_type', GoodieType::None->value);
        $this->controller->saveProfile($this->request);
        $this->assertFalse($this->user->settings->email_goodie);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresTShirtSizeIfDisabled(): void
    {
        $this->setUpProfileTest();
        $this->config->set('goodie_type', GoodieType::None->value);
        $this->controller->saveProfile($this->request);
        $this->assertEquals('', $this->user->personalData->shirt_size);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileSavesSupervisorStatusForAdult(): void
    {
        $this->setUpProfileTest();

        // Ensure user is NOT a minor (minor_category_id is null by default)
        $this->assertNull($this->user->minor_category_id);
        $this->assertFalse($this->user->isMinor());

        // Add willing_to_supervise to the request body
        $body = $this->request->getParsedBody();
        $body['willing_to_supervise'] = true;
        $this->request = $this->request->withParsedBody($body);

        $this->controller->saveProfile($this->request);

        // Reload the supervisor status from database
        $this->user->refresh();
        $supervisorStatus = UserSupervisorStatus::where('user_id', $this->user->id)->first();
        $this->assertNotNull($supervisorStatus);
        $this->assertTrue($supervisorStatus->willing_to_supervise);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveProfile
     */
    public function testSaveProfileIgnoresSupervisorStatusForMinor(): void
    {
        $this->setUpProfileTest();

        // Create a minor category and assign it to the user to make them a minor
        $minorCategory = MinorCategory::factory()->create();
        $this->user->minor_category_id = $minorCategory->id;
        $this->user->save();
        $this->assertTrue($this->user->isMinor());

        // Add willing_to_supervise to the request body
        $body = $this->request->getParsedBody();
        $body['willing_to_supervise'] = true;
        $this->request = $this->request->withParsedBody($body);

        $this->controller->saveProfile($this->request);

        // Check that no supervisor status was created for the minor
        $this->user->refresh();
        $supervisorStatus = UserSupervisorStatus::where('user_id', $this->user->id)->first();
        $this->assertNull($supervisorStatus);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::password
     */
    public function testPassword(): void
    {
        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
        ->method('withView')
        ->willReturnCallback(function ($view, $data) {
            $this->assertEquals('pages/settings/password', $view);

            return $this->response;
        });

        $this->controller->password();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePassword(): void
    {
        $body = [
            'password' => 'password',
            'new_password' => 'newpassword',
            'new_password2' => 'newpassword',
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

        $this->controller->savePassword($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('User set new password.'));

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages.' . NotificationType::MESSAGE->value);
        $this->assertEquals('settings.password.success', $messages[0]);

        $this->assertCount(
            1,
            SessionModel::whereUserId($this->user->id)->get(),
            'All other user sessions should be deleted after setting a new password'
        );
        $this->assertCount(2, SessionModel::all()); // Current session and another one should be still there
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordWhenEmpty(): void
    {
        $this->user->password = '';
        $this->user->save();

        $body = [
            'new_password'  => 'anotherpassword',
            'new_password2' => 'anotherpassword',
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

        $this->controller->savePassword($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordWrongOldPassword(): void
    {
        $body = [
            'password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password2' => 'newpassword',
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

        $this->controller->savePassword($this->request);

        $this->assertHasNotification('auth.password.error', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     */
    public function testSavePasswordMismatchingNewPassword(): void
    {
        $body = [
            'password' => 'password',
            'new_password' => 'newpassword',
            'new_password2' => 'wrongpassword',
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

        $this->controller->savePassword($this->request);

        $this->assertHasNotification('validation.password.confirmed', NotificationType::ERROR);
    }

    public function savePasswordValidationProvider(): array
    {
        return [
            [null, 'newpassword', 'newpassword'],
            ['password', null, 'newpassword'],
            ['password', 'newpassword', null],
            ['password', 'short', 'short'],
        ];
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::savePassword
     * @dataProvider savePasswordValidationProvider
     */
    public function testSavePasswordValidation(
        ?string $password,
        ?string $newPassword,
        ?string $newPassword2
    ): void {
        $body = [
            'password' => $password,
            'new_password' => $newPassword,
            'new_password2' => $newPassword2,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->auth, 'setPassword', null, null, $this->never());

        $this->expectException(ValidationException::class);

        $this->controller->savePassword($this->request);
    }

    /**
     * @testdox theme: underNormalConditions -> returnsCorrectViewAndData
     * @covers \Engelsystem\Controllers\SettingsController::theme
     */
    public function testThemeUnderNormalConditionReturnsCorrectViewAndData(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

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

        $this->controller->theme();
    }

    /**
     * @testdox saveTheme: withNoSelectedThemeGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithNoSelectedThemeGivenThrowsException(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        $this->controller->saveTheme($this->request);
    }

    /**
     * @testdox saveTheme: withUnknownSelectedThemeGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithUnknownSelectedThemeGivenThrowsException(): void
    {
        $this->request = $this->request->withParsedBody(['select_theme' => 2]);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        $this->controller->saveTheme($this->request);
    }

    /**
     * @testdox saveTheme: withKnownSelectedThemeGiven -> savesThemeAndRedirect
     * @covers \Engelsystem\Controllers\SettingsController::saveTheme
     */
    public function testSaveThemeWithKnownSelectedThemeGivenSavesThemeAndRedirect(): void
    {
        $this->assertEquals(1, $this->user->settings->theme);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/theme')
            ->willReturn($this->response);

        $this->request = $this->request->withParsedBody(['select_theme' => 0]);

        $this->controller->saveTheme($this->request);

        $this->assertEquals(0, $this->user->settings->theme);
    }

    /**
     * @testdox language: underNormalConditions -> returnsCorrectViewAndData
     * @covers \Engelsystem\Controllers\SettingsController::language
     */
    public function testLanguageUnderNormalConditionReturnsCorrectViewAndData(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/language', $view);
                $this->assertArrayHasKey('settings_menu', $data);
                $this->assertArrayHasKey('languages', $data);
                $this->assertArrayHasKey('current_language', $data);
                $this->assertEquals(['en_US' => 'language.en_US', 'de_DE' => 'language.de_DE'], $data['languages']);
                $this->assertEquals('en_US', $data['current_language']);

                return $this->response;
            });

        $this->controller->language();
    }

    /**
     * @testdox saveLanguage: withNoSelectedLanguageGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithNoSelectedLanguageGivenThrowsException(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        $this->controller->saveLanguage($this->request);
    }

    /**
     * @testdox saveLanguage: withUnknownSelectedLanguageGiven -> throwsException
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithUnknownSelectedLanguageGivenThrowsException(): void
    {
        $this->request = $this->request->withParsedBody(['select_language' => 'unknown']);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->expectException(ValidationException::class);

        $this->controller->saveLanguage($this->request);
    }

    /**
     * @testdox saveLanguage: withKnownSelectedLanguageGiven -> savesLanguageAndUpdatesSessionAndRedirect
     * @covers \Engelsystem\Controllers\SettingsController::saveLanguage
     */
    public function testSaveLanguageWithKnownSelectedLanguageGivenSavesLanguageAndUpdatesSessionAndRedirect(): void
    {
        $this->assertEquals('en_US', $this->user->settings->language);
        $this->session->set('locale', 'en_US');

        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/language')
            ->willReturn($this->response);

        $this->request = $this->request->withParsedBody(['select_language' => 'de_DE']);

        $this->controller->saveLanguage($this->request);

        $this->assertEquals('de_DE', $this->user->settings->language);
        $this->assertEquals('de_DE', $this->session->get('locale'));
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::__construct
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauth(): void
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        config(['oauth' => $providers]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($providers) {
                $this->assertEquals('pages/settings/oauth', $view);
                $this->assertArrayHasKey('providers', $data);
                $this->assertEquals($providers, $data['providers']);

                return $this->response;
            });

        $this->controller->oauth();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauthNotConfigured(): void
    {
        config(['oauth' => []]);

        $this->expectException(HttpNotFound::class);
        $this->controller->oauth();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::sessions
     */
    public function testSessions(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/sessions', $view);

                $this->assertArrayHasKey('sessions', $data);
                $this->assertCount(3, $data['sessions']);

                $this->assertArrayHasKey('current_session', $data);
                $this->assertEquals($this->currentSession->id, $data['current_session']);

                return $this->response;
            });

        $this->controller->sessions();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::sessionsDelete
     */
    public function testSessionsDelete(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->response, 'redirectTo', ['http://localhost/settings/sessions'], $this->response);

        // Delete old user session
        $this->request = $this->request->withParsedBody(['id' => Str::substr($this->secondSession->id, 0, 15)]);
        $this->controller->sessionsDelete($this->request);

        $this->assertHasNotification('settings.sessions.delete_success');
        $this->assertCount(3, SessionModel::all());
        $this->assertNull(SessionModel::find($this->secondSession->id));
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::sessionsDelete
     */
    public function testSessionsDeleteActiveSession(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->response, 'redirectTo', null, $this->response);

        // Delete active user session
        $this->request = $this->request->withParsedBody(['id' => Str::substr($this->currentSession->id, 0, 15)]);
        $this->controller->sessionsDelete($this->request);

        $this->assertCount(4, SessionModel::all()); // None got deleted
        $this->assertNotNull(SessionModel::find($this->currentSession->id));
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::sessionsDelete
     */
    public function testSessionsDeleteOtherUsersSession(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->response, 'redirectTo', null, $this->response);

        // Delete another users session
        $this->request = $this->request->withParsedBody(['id' => Str::substr($this->otherSession->id, 0, 15)]);
        $this->controller->sessionsDelete($this->request);

        $this->assertCount(4, SessionModel::all()); // None got deleted
        $this->assertNotNull(SessionModel::find($this->otherSession->id));
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::sessionsDelete
     */
    public function testSessionsDeleteAllSessions(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->user, $this->once());
        $this->setExpects($this->response, 'redirectTo', null, $this->response);

        // Delete all other user sessions
        $this->request = $this->request->withParsedBody(['id' => 'all']);
        $this->controller->sessionsDelete($this->request);

        $this->assertCount(2, SessionModel::all()); // Two got deleted
        $this->assertNotNull(SessionModel::find($this->currentSession->id));
        $this->assertNull(SessionModel::find($this->secondSession->id));
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::__construct
     * @covers \Engelsystem\Controllers\SettingsController::certificate
     */
    public function testCertificateIfsg(): void
    {
        config(['ifsg_enabled' => true, 'ifsg_light_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/certificates', $view);
                $this->assertArrayHasKey('certificates', $data);
                $this->assertEquals($this->user->license, $data['certificates']);
                return $this->response;
            });

        $this->controller->certificate();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::certificate
     */
    public function testCertificateIfsgNotConfigured(): void
    {
        config(['ifsg_enabled' => false]);

        $this->expectException(HttpNotFound::class);
        $this->controller->certificate();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::certificate
     */
    public function testCertificateDrivingLicense(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_driver_license' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/certificates', $view);
                $this->assertArrayHasKey('certificates', $data);
                $this->assertEquals($this->user->license, $data['certificates']);
                return $this->response;
            });

        $this->controller->certificate();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateNotConfigured(): void
    {
        config(['ifsg_enabled' => false]);

        $this->expectException(HttpNotFound::class);
        $this->controller->saveIfsgCertificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateLight(): void
    {
        config(['ifsg_enabled' => true, 'ifsg_light_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $body = [
            'ifsg_certificate_light' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertTrue($this->user->license->ifsg_certificate_light);
        $this->assertFalse($this->user->license->ifsg_certificate);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateLightWhileDisabled(): void
    {
        config(['ifsg_enabled' => true, 'ifsg_light_enabled' => false]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $this->user->license->ifsg_certificate_light = false;
        $this->user->license->save();

        $body = [
            'ifsg_certificate_light' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertFalse($this->user->license->ifsg_certificate_light);
        $this->assertFalse($this->user->license->ifsg_certificate);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveIfsgCertificate
     * @covers \Engelsystem\Controllers\SettingsController::checkIfsgCertificate
     */
    public function testSaveIfsgCertificate(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $body = [
            'ifsg_certificate' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertFalse($this->user->license->ifsg_certificate_light);
        $this->assertTrue($this->user->license->ifsg_certificate);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateBoth(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $body = [
            'ifsg_certificate_light' => true,
            'ifsg_certificate'       => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertFalse($this->user->license->ifsg_certificate_light);
        $this->assertTrue($this->user->license->ifsg_certificate);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveDrivingLicense
     * @covers \Engelsystem\Controllers\SettingsController::checkDrivingLicense
     */
    public function testSaveDrivingLicense(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_driver_license' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $body = [
            'has_car' => true,
            'drive_forklift' => true,
            'drive_12t' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/settings/certificates')
            ->willReturn($this->response);

        $this->controller->saveDrivingLicense($this->request);

        $this->assertTrue($this->user->license->has_car);
        $this->assertTrue($this->user->license->drive_forklift);
        $this->assertTrue($this->user->license->drive_12t);
        $this->assertFalse($this->user->license->drive_car);
        $this->assertFalse($this->user->license->drive_3_5t);
        $this->assertFalse($this->user->license->drive_7_5t);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::saveDrivingLicense
     */
    public function testSaveDrivingLicenseNotAvailable(): void
    {
        $this->expectException(HttpNotFound::class);
        $this->controller->saveDrivingLicense($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::api
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testApi(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        /** @var Response|MockObject $response */
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/settings/api', $view);
                $this->assertArrayHasKey('settings_menu', $data);
                return $this->response;
            });

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->api();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::apiKeyReset
     */
    public function testApiKeyReset(): void
    {
        $redirector = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $redirector);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());
        $this->setExpects($this->auth, 'resetApiKey', [$this->user], null, $this->atLeastOnce());
        $this->setExpects($redirector, 'back', null, $this->response);

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->apiKeyReset();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuProfile(): void
    {
        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/profile', $menu);
        $this->assertEquals('settings.profile', $menu['http://localhost/settings/profile']);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuPassword(): void
    {
        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/password', $menu);
        $this->assertEquals(
            ['title' => 'settings.password', 'icon' => 'key-fill'],
            $menu['http://localhost/settings/password']
        );
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuLanguage(): void
    {
        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/language', $menu);
        $this->assertEquals(
            ['title' => 'settings.language', 'icon' => 'translate'],
            $menu['http://localhost/settings/language']
        );
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     * @covers \Engelsystem\Controllers\SettingsController::checkOauthHidden
     */
    public function testSettingsMenuWithOAuth(): void
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        $providersHidden = ['foo' => ['lorem' => 'ipsum', 'hidden' => true]];
        config(['oauth' => $providers]);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/oauth', $menu);
        $this->assertEquals(['title' => 'settings.oauth', 'hidden' => false], $menu['http://localhost/settings/oauth']);

        config(['oauth' => $providersHidden]);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/oauth', $menu);
        $this->assertEquals(['title' => 'settings.oauth', 'hidden' => true], $menu['http://localhost/settings/oauth']);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::checkOauthHidden
     */
    public function testSettingsMenuWithOAuthShownWhenConnected(): void
    {
        // Provider configured as hidden
        $providersHidden = ['foo' => ['lorem' => 'ipsum', 'hidden' => true]];
        config(['oauth' => $providersHidden]);

        OAuth::factory()->create(['provider' => 'foo', 'user_id' => $this->user->id]);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/oauth', $menu);
        $this->assertEquals(['title' => 'settings.oauth', 'hidden' => false], $menu['http://localhost/settings/oauth']);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithoutOAuth(): void
    {
        config(['oauth' => []]);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayNotHasKey('http://localhost/settings/oauth', $menu);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithIfsg(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/certificates', $menu);
        $this->assertEquals(
            ['title' => 'settings.certificates', 'icon' => 'card-checklist'],
            $menu['http://localhost/settings/certificates']
        );
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithDrivingLicense(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->atLeastOnce());

        $angelType = AngelType::factory()->create(['requires_driver_license' => true]);
        $this->user->userAngelTypes()->attach($angelType);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/certificates', $menu);
        $this->assertEquals(
            ['title' => 'settings.certificates', 'icon' => 'card-checklist'],
            $menu['http://localhost/settings/certificates']
        );
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuWithoutIfsg(): void
    {
        config(['ifsg_enabled' => false]);

        $menu = $this->controller->settingsMenu();
        $this->assertArrayNotHasKey('http://localhost/settings/certificates', $menu);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuApi(): void
    {
        $this->setExpects($this->auth, 'canAny', null, true, $this->atLeastOnce());

        $menu = $this->controller->settingsMenu();
        $this->assertArrayHasKey('http://localhost/settings/profile', $menu);
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::settingsMenu
     */
    public function testSettingsMenuApiNotAvailable(): void
    {
        $menu = $this->controller->settingsMenu();
        $this->assertArrayNotHasKey('http://localhost/settings/api', $menu);
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        $themes = [
            0 => ['name' => 'Engelsystem light'],
            1 => ['name' => 'Engelsystem dark'],
        ];
        $languages = [
            'en_US',
            'de_DE',
        ];
        $tshirt_sizes = ['S' => 'Small'];
        $requiredFields = ['tshirt_size'];
        $this->config = new Config([
            'password_min_length' => 6,
            'themes' => $themes,
            'locales' => $languages,
            'tshirt_sizes' => $tshirt_sizes,
            'goodie_type' => GoodieType::Goodie->value,
            'required_user_fields' => $requiredFields,
        ]);
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);

        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->user = User::factory()
            ->has(Settings::factory([
                'theme' => 1,
                'language' => 'en_US',
                'email_goodie' => false,
                'mobile_show' => false,
            ]))
            ->has(License::factory())
            ->create();

        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());

        // Create 4 sessions, 3 for the active user
        $this->otherSession = SessionModel::factory()->create()->first(); // Other users sessions
        $sessions = SessionModel::factory(3)->create(['user_id' => $this->user->id]);
        $this->currentSession = $sessions->first();
        $this->secondSession = $sessions->last();
        $this->session->setId($this->currentSession->id);

        $this->controller = $this->app->make(SettingsController::class);
        $this->controller->setValidator(new Validator());
    }
}
