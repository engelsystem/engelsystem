<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\NotificationType;
use Engelsystem\Controllers\RegistrationController;
use Engelsystem\Factories\User;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\User\User as EngelsystemUser;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group registration-controller-minor-tests
 * @covers \Engelsystem\Controllers\RegistrationController
 */
final class RegistrationControllerMinorTest extends ControllerTest
{
    use HasDatabase;

    private Authenticator|MockObject $authenticator;

    private User|MockObject $userFactory;

    private RegistrationController $subject;

    private MinorCategory $category;

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

        // Create a minor category for testing
        $this->category = MinorCategory::factory()->active()->create([
            'name' => 'Teen (13-15)',
            'can_self_signup' => true,
        ]);

        $this->subject = $this->app->make(RegistrationController::class);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::save
     */
    public function testSaveMinorRedirectsToGuardianLinking(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('enable_minor_registration', true);

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);

        // Create a minor user
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);

        $this->userFactory
            ->expects(self::once())
            ->method('createFromData')
            ->with($userData)
            ->willReturn($minorUser);

        // Assert that the user is redirected to guardian linking
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register/link-guardian', 302);

        $this->subject->save($request);

        // Assert that pending_minor_user_id is set in session
        $this->assertEquals($minorUser->id, $this->session->get('pending_minor_user_id'));
        $this->assertHasNotification('registration.minor.link_guardian', NotificationType::INFORMATION);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::save
     */
    public function testSaveAdultDoesNotRedirectToGuardianLinking(): void
    {
        $this->setPasswordRegistrationEnabledConfig();

        $userData = ['user' => 'data'];
        $request = $this->request->withParsedBody($userData);

        // Create an adult user (no minor_category_id)
        $adultUser = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);

        $this->userFactory
            ->expects(self::once())
            ->method('createFromData')
            ->with($userData)
            ->willReturn($adultUser);

        // Assert that the user is redirected to home (not guardian linking)
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->save($request);

        // Assert that pending_minor_user_id is NOT set in session
        $this->assertFalse($this->session->has('pending_minor_user_id'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::linkGuardian
     */
    public function testLinkGuardianShowsPage(): void
    {
        // Create a minor user and set session
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $this->session->set('pending_minor_user_id', $minorUser->id);

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($minorUser) {
                $this->assertEquals('pages/register/link-guardian', $view);
                $this->assertArrayHasKey('minor', $data);
                $this->assertEquals($minorUser->id, $data['minor']->id);
                return $this->response;
            });

        $this->subject->linkGuardian();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::linkGuardian
     */
    public function testLinkGuardianRedirectsWithoutSession(): void
    {
        // No session set - should redirect to home
        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->linkGuardian();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::linkGuardian
     */
    public function testLinkGuardianRedirectsWhenMinorNotFound(): void
    {
        // Session has a user ID that doesn't exist
        $this->session->set('pending_minor_user_id', 99999);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->linkGuardian();

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::linkGuardian
     */
    public function testLinkGuardianRedirectsWhenUserNoLongerMinor(): void
    {
        // Create a user that is not a minor (category was removed)
        $adultUser = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);
        $this->session->set('pending_minor_user_id', $adultUser->id);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->linkGuardian();

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianRedirectsWithoutSession(): void
    {
        // No session set - should redirect to home
        $request = $this->request->withParsedBody([
            'guardian_identifier' => 'some_user',
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianRedirectsWhenMinorNotFound(): void
    {
        // Session has a user ID that doesn't exist
        $this->session->set('pending_minor_user_id', 99999);

        $request = $this->request->withParsedBody([
            'guardian_identifier' => 'some_user',
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianRedirectsWhenUserNoLongerMinor(): void
    {
        // Create a user that is not a minor (category was removed)
        $adultUser = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);
        $this->session->set('pending_minor_user_id', $adultUser->id);

        $request = $this->request->withParsedBody([
            'guardian_identifier' => 'some_user',
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianSuccessByEmail(): void
    {
        // Create a minor user and guardian
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $guardian = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);

        $this->session->set('pending_minor_user_id', $minorUser->id);

        // Find guardian by email instead of username
        $request = $this->request->withParsedBody([
            'guardian_identifier' => $guardian->email,
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        // Assert guardian link was created
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minorUser->id)
            ->first();
        $this->assertNotNull($link);

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));

        $this->assertHasNotification('registration.guardian_linked');
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianSuccess(): void
    {
        // Create a minor user and guardian
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $guardian = EngelsystemUser::factory()->create([
            'minor_category_id' => null,
        ]);

        $this->session->set('pending_minor_user_id', $minorUser->id);

        $request = $this->request->withParsedBody([
            'guardian_identifier' => $guardian->name,
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        // Assert guardian link was created
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minorUser->id)
            ->first();
        $this->assertNotNull($link);

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));

        $this->assertHasNotification('registration.guardian_linked');
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianNotFound(): void
    {
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $this->session->set('pending_minor_user_id', $minorUser->id);

        $request = $this->request->withParsedBody([
            'guardian_identifier' => 'nonexistent_user',
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register/link-guardian', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        $this->assertHasNotification('registration.guardian_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::saveLinkGuardian
     */
    public function testSaveLinkGuardianIsMinor(): void
    {
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $otherMinor = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);

        $this->session->set('pending_minor_user_id', $minorUser->id);

        $request = $this->request->withParsedBody([
            'guardian_identifier' => $otherMinor->name,
        ]);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/register/link-guardian', 302);

        $this->subject->setValidator(new Validator());
        $this->subject->saveLinkGuardian($request);

        $this->assertHasNotification('registration.guardian_is_minor', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::skipLinkGuardian
     */
    public function testSkipLinkGuardian(): void
    {
        $minorUser = EngelsystemUser::factory()->create([
            'minor_category_id' => $this->category->id,
        ]);
        $this->session->set('pending_minor_user_id', $minorUser->id);

        $this->response
            ->expects(self::once())
            ->method('redirectTo')
            ->with('http://localhost/', 302);

        $this->subject->skipLinkGuardian();

        // Assert session was cleared
        $this->assertFalse($this->session->has('pending_minor_user_id'));

        $this->assertHasNotification('registration.guardian_link_skipped', NotificationType::WARNING);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::view
     */
    public function testViewIncludesMinorCategories(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('enable_minor_registration', true);

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/registration', $view);
                $this->assertArrayHasKey('isMinorRegistrationEnabled', $data);
                $this->assertTrue($data['isMinorRegistrationEnabled']);
                $this->assertArrayHasKey('minorCategories', $data);
                // At least the one we created should be present
                $this->assertGreaterThanOrEqual(1, count($data['minorCategories']));
                // Verify our category is in there
                $found = false;
                foreach ($data['minorCategories'] as $cat) {
                    if ($cat->id === $this->category->id) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found, 'Test category should be in the list');
                return $this->response;
            });

        $this->subject->view();
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController::view
     */
    public function testViewExcludesMinorCategoriesWhenDisabled(): void
    {
        $this->setPasswordRegistrationEnabledConfig();
        $this->config->set('enable_minor_registration', false);

        $this->response
            ->expects(self::once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/registration', $view);
                $this->assertArrayHasKey('isMinorRegistrationEnabled', $data);
                $this->assertFalse($data['isMinorRegistrationEnabled']);
                $this->assertArrayHasKey('minorCategories', $data);
                $this->assertCount(0, $data['minorCategories']); // Empty collection when disabled
                return $this->response;
            });

        $this->subject->view();
    }

    private function setPasswordRegistrationEnabledConfig(): void
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
            ->willReturn(true);
        $this->userFactory->method('determineBuildUpStartDate')
            ->willReturn(new \DateTimeImmutable());
    }
}
