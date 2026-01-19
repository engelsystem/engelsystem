<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\RegistrationController;
use Engelsystem\Factories\User;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\User as EngelsystemUser;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RegistrationController::class)]
#[Group('registration-controller-tests')]
#[AllowMockObjectsWithoutExpectations]
final class RegistrationControllerTest extends ControllerTestCase
{
    /**
     * @var Authenticator&MockObject
     */
    private Authenticator $authenticator;

    /**
     * @var MockObject&User
     */
    private User $userFactory;

    private RegistrationController $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->stubTranslator();

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
        $this->subject = $this->app->make(RegistrationController::class);
    }

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

    public function testSaveAlreadyLoggedIn(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $request = $this->request->withParsedBody(['user' => 'data']);

        // Fake logged-in user
        $this->authenticator->method('user')->willReturn(new EngelsystemUser());

        // Assert that the user is redirected to /register again
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register', 302);

        $this->subject->save($request);
    }

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
