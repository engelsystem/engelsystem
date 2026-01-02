<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\RegistrationController;
use Engelsystem\Events\Listener\OAuth2;
use Engelsystem\Factories\User;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\User as EngelsystemUser;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group registration-controller-tests
 * @covers \Engelsystem\Controllers\RegistrationController
 */
final class RegistrationControllerTest extends ControllerTest
{
    use HasDatabase;

    /**
     * @var Authenticator&MockObject
     */
    private Authenticator $authenticator;

    /**
     * @var MockObject&User
     */
    private User $userFactory;

    /**
     * @var OAuth2&MockObject
     */
    private OAuth2 $oauth2;

    private RegistrationController $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockTranslator();
        $this->initDatabase();

        $this->authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instance(Authenticator::class, $this->authenticator);
        $this->app->alias(Authenticator::class, 'authenticator');

        $this->userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->set('oauth', []);
        $this->app->instance(User::class, $this->userFactory);

        $this->oauth2 = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instance(OAuth2::class, $this->oauth2);

        $this->subject = $this->app->make(RegistrationController::class);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testSave(): void
    {
        $this->setPasswordRegistrationEnabledConfig();

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);

        // Assert the controller passes the submitted data to the user factory
        $this->userFactory
            ->expects(self::once())
            ->method('createFromData')
            ->with($userData)
            ->willReturn(new EngelsystemUser());

        // Assert that the user is redirected to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->save($request);

        // Assert that the success notification is there
        self::assertEquals(
            [
                'messages.message' => ['registration.successful'],
            ],
            $this->session->all()
        );

        // Assert that "show_welcome" is not set in session,
        // because "welcome_msg" is not configured.
        $this->assertFalse($this->session->has('show_welcome'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testSaveAlreadyLoggedIn(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $request = $this->request->withParsedBody(['user' => 'data']);

        // Fake logged in user
        $this->authenticator->method('user')->willReturn(new EngelsystemUser());

        // Assert that the user is redirected to /register again
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register', 302);

        $this->subject->save($request);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testSaveOAuth(): void
    {
        $this->setPasswordRegistrationEnabledConfig();

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);

        $user = (new EngelsystemUser())->factory()->create();
        $oauth = (new OAuth())->factory()->create([
            'user_id' => $user->id,
        ]);

        $this->userFactory
            ->expects(self::once())
            ->method('createFromData')
            ->with($userData)
            ->willReturn($user);

        // Assert that the user is redirected to the OAuth login
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/oauth/' . $oauth->provider, 302);

        $this->subject->save($request);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testSaveWithWelcomeMesssage(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('welcome_msg', true);

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);
        $this->subject->save($request);

        // Assert that "show_welcome" is set in session,
        // because "welcome_msg" is enabled in the config.
        $this->assertTrue($this->session->get('show_welcome'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testSaveRegistrationDisabled(): void
    {
        $this->config->set('registration_enabled', false);
        $request = $this->request->withParsedBody([]);

        // Assert the controller does not call createFromData
        $this->userFactory
            ->expects(self::never())
            ->method('createFromData');

        // Assert that the user is redirected to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->save($request);

        // Assert that the error notification is there
        self::assertEquals(
            [
                'messages.information' => ['registration.disabled'],
            ],
            $this->session->all()
        );
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewRegistrationDisabled(): void
    {
        $this->config->set('registration_enabled', false);

        // Assert the controller does not call createFromData
        $this->userFactory
            ->expects(self::never())
            ->method('createFromData');

        // Assert that the user is redirected to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->view();

        // Assert that the error notification is there
        self::assertEquals(
            [
                'messages.information' => ['registration.disabled'],
            ],
            $this->session->all()
        );
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewWithOAuthPreselectsAngelTypes(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('goodie_type', 'none');
        $this->config->set('tshirt_sizes', []);
        $this->config->set('required_user_fields', []);
        $this->config->set('enable_dect', false);
        $this->config->set('enable_mobile_show', false);
        $this->config->set('enable_full_name', false);
        $this->config->set('enable_pronoun', false);
        $this->config->set('enable_planned_arrival', false);

        // Set OAuth session data
        $this->session->set('oauth2_connect_provider', 'test_provider');
        $this->session->set('oauth2_groups', ['TestTeam']);

        // Mock getSsoTeams to return teams mapping
        $this->oauth2->method('getSsoTeams')
            ->with('test_provider')
            ->willReturn([
                'TestTeam' => ['id' => 1],
            ]);

        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/registration', $view);
                $this->assertArrayHasKey('preselectedAngelTypes', $data);
                $this->assertArrayHasKey('angel_types_1', $data['preselectedAngelTypes']);
                return $this->response;
            });

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewWithFormDataPreselectsAngelTypes(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('goodie_type', 'none');
        $this->config->set('tshirt_sizes', []);
        $this->config->set('required_user_fields', []);
        $this->config->set('enable_dect', false);
        $this->config->set('enable_mobile_show', false);
        $this->config->set('enable_full_name', false);
        $this->config->set('enable_pronoun', false);
        $this->config->set('enable_planned_arrival', false);

        // Create an AngelType in the database
        $angelType = AngelType::factory()->create([
            'hide_register' => false,
        ]);

        // Set form-data session marker
        $this->session->set('form-data-register-submit', '1');
        // Set the specific angel type as selected in form data
        $this->session->set('form-data-angel_types_' . $angelType->id, '1');

        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($angelType) {
                $this->assertEquals('pages/registration', $view);
                $this->assertArrayHasKey('preselectedAngelTypes', $data);
                $this->assertArrayHasKey('angel_types_' . $angelType->id, $data['preselectedAngelTypes']);
                return $this->response;
            });

        $this->subject->view();

        // Assert form-data-register-submit was cleared
        $this->assertFalse($this->session->has('form-data-register-submit'));
        // Assert angel type form data was cleared
        $this->assertFalse($this->session->has('form-data-angel_types_' . $angelType->id));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewPasswordDisabledNoOAuthRedirectsHome(): void
    {
        $this->config->set('registration_enabled', true);
        $this->authenticator
            ->method('can')
            ->with('register')
            ->willReturn(true);
        $this->userFactory->method('determineIsPasswordEnabled')
            ->willReturn(false);

        // Assert that the user is redirected to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewPasswordDisabledWithOAuthAllowed(): void
    {
        $this->config->set('registration_enabled', true);
        $this->config->set('goodie_type', 'none');
        $this->config->set('tshirt_sizes', []);
        $this->config->set('required_user_fields', []);
        $this->config->set('enable_dect', false);
        $this->config->set('enable_mobile_show', false);
        $this->config->set('enable_full_name', false);
        $this->config->set('enable_pronoun', false);
        $this->config->set('enable_planned_arrival', false);

        $this->authenticator
            ->method('can')
            ->with('register')
            ->willReturn(true);
        $this->userFactory->method('determineIsPasswordEnabled')
            ->willReturn(false);
        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());

        // Set OAuth session data to allow registration
        $this->session->set('oauth2_connect_provider', 'test_provider');

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/registration', $view);
                return $this->response;
            });

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewRegistrationDisabledWithOAuthAllowRegistration(): void
    {
        $this->config->set('registration_enabled', false);
        $this->config->set('goodie_type', 'none');
        $this->config->set('tshirt_sizes', []);
        $this->config->set('required_user_fields', []);
        $this->config->set('enable_dect', false);
        $this->config->set('enable_mobile_show', false);
        $this->config->set('enable_full_name', false);
        $this->config->set('enable_pronoun', false);
        $this->config->set('enable_planned_arrival', false);

        $this->authenticator
            ->method('can')
            ->with('register')
            ->willReturn(true);
        $this->userFactory->method('determineIsPasswordEnabled')
            ->willReturn(true);
        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());

        // Set OAuth allow registration flag
        $this->session->set('oauth2_allow_registration', true);

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/registration', $view);
                return $this->response;
            });

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewNoRegisterPermission(): void
    {
        $this->config->set('registration_enabled', true);
        $this->authenticator
            ->method('can')
            ->with('register')
            ->willReturn(false);
        $this->userFactory->method('determineIsPasswordEnabled')
            ->willReturn(true);

        // Assert that the user is redirected to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewPreselectsUnrestrictedAngelTypes(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('goodie_type', 'none');
        $this->config->set('tshirt_sizes', []);
        $this->config->set('required_user_fields', []);
        $this->config->set('enable_dect', false);
        $this->config->set('enable_mobile_show', false);
        $this->config->set('enable_full_name', false);
        $this->config->set('enable_pronoun', false);
        $this->config->set('enable_planned_arrival', false);

        // Create an unrestricted, non-hidden AngelType
        $angelType = AngelType::factory()->create([
            'restricted' => false,
            'hide_register' => false,
        ]);

        // Do NOT set form-data-register-submit so we go through the unrestricted angel types path
        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($angelType) {
                $this->assertEquals('pages/registration', $view);
                $this->assertArrayHasKey('preselectedAngelTypes', $data);
                // The unrestricted angel type should be preselected
                $this->assertArrayHasKey('angel_types_' . $angelType->id, $data['preselectedAngelTypes']);
                return $this->response;
            });

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::save
     */
    public function testSaveSupporterCreatesUserAndRedirectsToRegister(): void
    {
        $this->setPasswordRegistrationEnabledConfig();

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);

        // Create a new user (adult, no OAuth)
        $newUser = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);

        // Fake logged in supporter user
        $supporter = new EngelsystemUser();
        $this->authenticator->method('user')->willReturn($supporter);

        // Assert the controller passes the submitted data to the user factory
        $this->userFactory
            ->expects(self::once())
            ->method('createFromData')
            ->with($userData)
            ->willReturn($newUser);

        // Assert that the supporter is redirected back to /register
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register', 302);

        $this->subject->save($request);

        // Assert that the supporter success notification is there
        self::assertEquals(
            [
                'messages.message' => ['registration.successful.supporter'],
            ],
            $this->session->all()
        );
    }

    private function setPasswordRegistrationEnabledConfig(): void
    {
        $this->config->set('registration_enabled', true);
        $this->authenticator
            ->method('can')
            ->with('register')
            ->willReturn(true);
        $this->userFactory->method('determineIsPasswordEnabled')
            ->willReturn(true);
    }
}
