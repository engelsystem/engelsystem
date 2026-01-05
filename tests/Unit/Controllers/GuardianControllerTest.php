<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\GuardianController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\User\User;
use Engelsystem\Services\GuardianService;
use Engelsystem\Services\MinorRestrictionService;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Engelsystem\Controllers\GuardianController
 * @uses \Engelsystem\Models\MinorCategory
 * @uses \Engelsystem\Models\UserGuardian
 * @uses \Engelsystem\Services\GuardianService
 * @uses \Engelsystem\Services\MinorRestrictionService
 */
class GuardianControllerTest extends ControllerTest
{
    use HasDatabase;

    protected Authenticator|MockObject $auth;
    protected Redirector|MockObject $redirector;
    protected GuardianService $guardianService;
    protected MinorRestrictionService $minorService;
    protected GuardianController $controller;

    protected User $guardian;
    protected User $minor;
    protected MinorCategory $category;

    /**
     * @covers \Engelsystem\Controllers\GuardianController::dashboard
     */
    public function testDashboard(): void
    {
        // First link guardian to minor so we have data to map
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/dashboard.twig', $view);
                $this->assertArrayHasKey('minors', $data);
                // Verify the minors data has the enriched fields
                $this->assertCount(1, $data['minors']);
                $minorData = $data['minors']->first();
                $this->assertArrayHasKey('user', $minorData);
                $this->assertArrayHasKey('category', $minorData);
                $this->assertArrayHasKey('restrictions', $minorData);
                $this->assertArrayHasKey('consentApproved', $minorData);
                $this->assertArrayHasKey('upcomingShifts', $minorData);
                $this->assertArrayHasKey('isPrimary', $minorData);
                $this->assertArrayHasKey('dailyHoursUsed', $minorData);
                return $this->response;
            });

        $this->controller->dashboard();
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::dashboard
     */
    public function testDashboardRedirectsForMinor(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->minor, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/'], $this->response);

        $this->controller->dashboard();

        $this->assertHasNotification('guardian.not_eligible', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::linkMinor
     */
    public function testLinkMinor(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view) {
                $this->assertEquals('pages/guardian/link-minor.twig', $view);
                return $this->response;
            });

        $this->controller->linkMinor();
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::linkMinor
     */
    public function testLinkMinorRedirectsForMinor(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->minor, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/'], $this->response);

        $this->controller->linkMinor();
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveLinkMinor
     */
    public function testSaveLinkMinor(): void
    {
        // Create a new minor to link
        /** @var User $newMinor */
        $newMinor = User::factory()->create(['minor_category_id' => $this->category->id]);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withParsedBody([
            'minor_identifier'  => $newMinor->name,
            'relationship_type' => 'parent',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveLinkMinor($this->request);

        $this->assertHasNotification('guardian.link_successful');
        $this->assertTrue($this->guardianService->canManageMinor($this->guardian, $newMinor));
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveLinkMinor
     */
    public function testSaveLinkMinorNotFound(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/link'], $this->response);

        $this->request = $this->request->withParsedBody([
            'minor_identifier'  => 'nonexistent_user',
            'relationship_type' => 'parent',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveLinkMinor($this->request);

        $this->assertHasNotification('guardian.minor_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveLinkMinor
     */
    public function testSaveLinkMinorNotActuallyMinor(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/link'], $this->response);

        $this->request = $this->request->withParsedBody([
            'minor_identifier'  => $adult->name,
            'relationship_type' => 'parent',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveLinkMinor($this->request);

        $this->assertHasNotification('guardian.user_not_minor', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::registerMinor
     */
    public function testRegisterMinor(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/register-minor.twig', $view);
                $this->assertArrayHasKey('categories', $data);
                return $this->response;
            });

        $this->controller->registerMinor();
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveRegisterMinor
     */
    public function testSaveRegisterMinor(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->redirector->expects($this->once())
            ->method('to')
            ->willReturnCallback(function ($url) {
                $this->assertStringStartsWith('/guardian/minor/', $url);
                return $this->response;
            });

        $this->request = $this->request->withParsedBody([
            'name'              => 'NewTestMinor',
            'email'             => 'newminor@test.com',
            'password'          => 'password123',
            'minor_category_id' => $this->category->id,
            'first_name'        => 'New',
            'last_name'         => 'Minor',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveRegisterMinor($this->request);

        $this->assertHasNotification('guardian.registration_successful');

        $newMinor = User::whereName('NewTestMinor')->first();
        $this->assertNotNull($newMinor);
        $this->assertTrue($this->guardianService->isPrimaryGuardian($this->guardian, $newMinor));
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveRegisterMinor
     */
    public function testSaveRegisterMinorDuplicateName(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/register'], $this->response);

        $this->request = $this->request->withParsedBody([
            'name'              => $this->minor->name, // Already exists
            'password'          => 'password123',
            'minor_category_id' => $this->category->id,
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveRegisterMinor($this->request);

        $this->assertHasNotification('registration.nick.taken', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::viewMinor
     */
    public function testViewMinor(): void
    {
        // Link guardian to minor first
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/minor-profile.twig', $view);
                $this->assertArrayHasKey('minor', $data);
                $this->assertArrayHasKey('category', $data);
                $this->assertArrayHasKey('restrictions', $data);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->viewMinor($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::viewMinor
     */
    public function testViewMinorNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->viewMinor($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::editMinor
     */
    public function testEditMinor(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/edit-minor.twig', $view);
                $this->assertArrayHasKey('minor', $data);
                $this->assertArrayHasKey('categories', $data);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->editMinor($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveMinor
     */
    public function testSaveMinor(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'email'      => 'updated@test.com',
                'first_name' => 'Updated',
                'last_name'  => 'Name',
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveMinor($this->request);

        $this->assertHasNotification('guardian.profile_updated');
        $this->minor->refresh();
        $this->assertEquals('updated@test.com', $this->minor->email);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::consentForm
     */
    public function testConsentForm(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/consent-form.twig', $view);
                $this->assertArrayHasKey('minor', $data);
                $this->assertArrayHasKey('guardian', $data);
                $this->assertArrayHasKey('category', $data);
                $this->assertArrayHasKey('restrictions', $data);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->consentForm($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::minorShifts
     */
    public function testMinorShifts(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/guardian/minor-shifts.twig', $view);
                $this->assertArrayHasKey('minor', $data);
                $this->assertArrayHasKey('restrictions', $data);
                $this->assertArrayHasKey('upcomingShifts', $data);
                $this->assertArrayHasKey('shiftHistory', $data);
                return $this->response;
            });

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->minorShifts($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::addGuardian
     */
    public function testAddGuardian(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $newGuardian */
        $newGuardian = User::factory()->create(['minor_category_id' => null]);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'guardian_identifier' => $newGuardian->name,
                'relationship_type'   => 'delegated',
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->addGuardian($this->request);

        $this->assertHasNotification('guardian.guardian_added');
        $this->assertTrue($this->guardianService->canManageMinor($newGuardian, $this->minor));
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::removeGuardian
     */
    public function testRemoveGuardian(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        $this->guardianService->linkGuardianToMinor($secondaryGuardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $secondaryGuardian->id);

        $this->controller->removeGuardian($this->request);

        $this->assertHasNotification('guardian.guardian_removed');
        $this->assertFalse($this->guardianService->canManageMinor($secondaryGuardian, $this->minor));
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::setPrimaryGuardian
     */
    public function testSetPrimaryGuardian(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        $this->guardianService->linkGuardianToMinor($secondaryGuardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $secondaryGuardian->id);

        $this->controller->setPrimaryGuardian($this->request);

        $this->assertHasNotification('guardian.primary_changed');
        $this->assertTrue($this->guardianService->isPrimaryGuardian($secondaryGuardian, $this->minor));
        $this->assertFalse($this->guardianService->isPrimaryGuardian($this->guardian, $this->minor));
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::changeCategory
     */
    public function testChangeCategory(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $newCategory = MinorCategory::factory()->active()->create(['name' => 'New Category']);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'minor_category_id' => $newCategory->id,
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->changeCategory($this->request);

        $this->assertHasNotification('guardian.category_changed');
        $this->minor->refresh();
        $this->assertEquals($newCategory->id, $this->minor->minor_category_id);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::changeCategory
     */
    public function testChangeCategoryInvalidCategory(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'minor_category_id' => 99999, // Non-existent
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->changeCategory($this->request);

        $this->assertHasNotification('guardian.invalid_category', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::changeCategory
     */
    public function testChangeCategoryInactiveCategory(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $inactiveCategory = MinorCategory::factory()->create(['is_active' => false]);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'minor_category_id' => $inactiveCategory->id,
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->changeCategory($this->request);

        $this->assertHasNotification('Category is not active', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::changeCategory
     */
    public function testChangeCategoryNotLinked(): void
    {
        // Don't link guardian to minor
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody(['minor_category_id' => $this->category->id]);

        $this->controller->setValidator(new Validator());
        $this->controller->changeCategory($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::registerMinor
     */
    public function testRegisterMinorNotEligible(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->minor, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/'], $this->response);

        $this->controller->registerMinor();
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveRegisterMinor
     */
    public function testSaveRegisterMinorNotEligible(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->minor, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/'], $this->response);

        $this->request = $this->request->withParsedBody([
            'name'              => 'TestMinor',
            'password'          => 'password123',
            'minor_category_id' => $this->category->id,
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveRegisterMinor($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveRegisterMinor
     */
    public function testSaveRegisterMinorInvalidCategory(): void
    {
        $inactiveCategory = MinorCategory::factory()->create(['is_active' => false]);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/register'], $this->response);

        $this->request = $this->request->withParsedBody([
            'name'              => 'UniqueTestMinor123',
            'password'          => 'password123',
            'minor_category_id' => $inactiveCategory->id,
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveRegisterMinor($this->request);

        $this->assertHasNotification('Invalid or inactive minor category', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::editMinor
     */
    public function testEditMinorNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->editMinor($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveMinor
     */
    public function testSaveMinorNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody(['email' => 'test@test.com']);

        $this->controller->setValidator(new Validator());
        $this->controller->saveMinor($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::consentForm
     */
    public function testConsentFormNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->consentForm($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }


    /**
     * @covers \Engelsystem\Controllers\GuardianController::minorShifts
     */
    public function testMinorShiftsNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withAttribute('minor_id', $this->minor->id);
        $this->controller->minorShifts($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::addGuardian
     */
    public function testAddGuardianNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'guardian_identifier' => 'someone',
                'relationship_type'   => 'delegated',
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->addGuardian($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::addGuardian
     */
    public function testAddGuardianNotFound(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'guardian_identifier' => 'nonexistent_user',
                'relationship_type'   => 'delegated',
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->addGuardian($this->request);

        $this->assertHasNotification('guardian.guardian_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::addGuardian
     */
    public function testAddGuardianNotPrimary(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        $this->guardianService->linkGuardianToMinor($secondaryGuardian, $this->minor);

        /** @var User $newGuardian */
        $newGuardian = User::factory()->create(['minor_category_id' => null]);

        // Act as secondary guardian
        $this->setExpects($this->auth, 'user', null, $secondaryGuardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody([
                'guardian_identifier' => $newGuardian->name,
                'relationship_type'   => 'delegated',
            ]);

        $this->controller->setValidator(new Validator());
        $this->controller->addGuardian($this->request);

        $this->assertHasNotification('Only the primary guardian can add other guardians', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::removeGuardian
     */
    public function testRemoveGuardianNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $this->guardian->id);

        $this->controller->removeGuardian($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::removeGuardian
     */
    public function testRemoveGuardianNotFound(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', 99999);

        $this->controller->removeGuardian($this->request);

        $this->assertHasNotification('guardian.guardian_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::removeGuardian
     */
    public function testRemoveGuardianPrimaryCannotRemoveSelf(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $this->guardian->id);

        $this->controller->removeGuardian($this->request);

        $this->assertHasNotification('Primary guardian cannot remove themselves', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::setPrimaryGuardian
     */
    public function testSetPrimaryGuardianNotLinked(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $this->guardian->id);

        $this->controller->setPrimaryGuardian($this->request);

        $this->assertHasNotification('guardian.no_access', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::setPrimaryGuardian
     */
    public function testSetPrimaryGuardianNotPrimary(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        $this->guardianService->linkGuardianToMinor($secondaryGuardian, $this->minor);

        // Act as secondary guardian
        $this->setExpects($this->auth, 'user', null, $secondaryGuardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $this->guardian->id);

        $this->controller->setPrimaryGuardian($this->request);

        $this->assertHasNotification('guardian.not_primary', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::setPrimaryGuardian
     */
    public function testSetPrimaryGuardianNewPrimaryNotFound(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', 99999);

        $this->controller->setPrimaryGuardian($this->request);

        $this->assertHasNotification('guardian.guardian_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveLinkMinor
     */
    public function testSaveLinkMinorNotEligible(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->minor, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/'], $this->response);

        $this->request = $this->request->withParsedBody([
            'minor_identifier'  => 'someone',
            'relationship_type' => 'parent',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveLinkMinor($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveLinkMinor
     */
    public function testSaveLinkMinorAlreadyLinked(): void
    {
        // Create and link a minor first
        /** @var User $linkedMinor */
        $linkedMinor = User::factory()->create(['minor_category_id' => $this->category->id]);
        $this->guardianService->linkGuardianToMinor($this->guardian, $linkedMinor);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/link'], $this->response);

        $this->request = $this->request->withParsedBody([
            'minor_identifier'  => $linkedMinor->name,
            'relationship_type' => 'parent',
        ]);

        $this->controller->setValidator(new Validator());
        $this->controller->saveLinkMinor($this->request);

        $this->assertHasNotification('Guardian is already linked to this minor', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::viewMinor
     */
    public function testViewMinorMinorNotFound(): void
    {
        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian'], $this->response);

        $this->request = $this->request->withAttribute('minor_id', 99999);
        $this->controller->viewMinor($this->request);

        $this->assertHasNotification('guardian.minor_not_found', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::__construct
     */
    public function testPermissions(): void
    {
        $this->assertEquals(['user_guardian'], $this->controller->getPermissions());
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::saveMinor
     */
    public function testSaveMinorServiceException(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        // Create a mock GuardianService that throws on updateMinorProfile
        $mockGuardianService = $this->createMock(GuardianService::class);
        $mockGuardianService->method('isEligibleGuardian')->willReturn(true);
        $mockGuardianService->method('canManageMinor')->willReturn(true);
        $mockGuardianService->method('updateMinorProfile')
            ->willThrowException(new \InvalidArgumentException('Test exception'));

        // Recreate controller with mock service
        $this->app->instance(GuardianService::class, $mockGuardianService);
        $controller = $this->app->make(GuardianController::class);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withParsedBody(['email' => 'test@test.com']);

        $controller->setValidator(new Validator());
        $controller->saveMinor($this->request);

        $this->assertHasNotification('Test exception', NotificationType::ERROR);
    }

    /**
     * @covers \Engelsystem\Controllers\GuardianController::setPrimaryGuardian
     */
    public function testSetPrimaryGuardianServiceException(): void
    {
        $this->guardianService->linkGuardianToMinor($this->guardian, $this->minor);

        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        $this->guardianService->linkGuardianToMinor($secondaryGuardian, $this->minor);

        // Create a mock GuardianService that throws on setPrimaryGuardian
        $mockGuardianService = $this->createMock(GuardianService::class);
        $mockGuardianService->method('isEligibleGuardian')->willReturn(true);
        $mockGuardianService->method('canManageMinor')->willReturn(true);
        $mockGuardianService->method('isPrimaryGuardian')->willReturn(true);
        $mockGuardianService->method('setPrimaryGuardian')
            ->willThrowException(new \InvalidArgumentException('Test set primary exception'));

        // Recreate controller with mock service
        $this->app->instance(GuardianService::class, $mockGuardianService);
        $controller = $this->app->make(GuardianController::class);

        $this->setExpects($this->auth, 'user', null, $this->guardian, $this->atLeastOnce());
        $this->setExpects($this->redirector, 'to', ['/guardian/minor/' . $this->minor->id], $this->response);

        $this->request = $this->request
            ->withAttribute('minor_id', $this->minor->id)
            ->withAttribute('guardian_id', $secondaryGuardian->id);

        $controller->setPrimaryGuardian($this->request);

        $this->assertHasNotification('Test set primary exception', NotificationType::ERROR);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->redirector = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirector);

        $this->minorService = new MinorRestrictionService();
        $this->app->instance(MinorRestrictionService::class, $this->minorService);

        $this->guardianService = new GuardianService($this->minorService);
        $this->app->instance(GuardianService::class, $this->guardianService);

        $this->category = MinorCategory::factory()->active()->create();
        $this->guardian = User::factory()->create(['minor_category_id' => null]);
        $this->minor = User::factory()->create(['minor_category_id' => $this->category->id]);

        $this->controller = $this->app->make(GuardianController::class);
    }
}
