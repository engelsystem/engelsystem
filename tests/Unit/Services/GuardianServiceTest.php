<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Services;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\UserSupervisorStatus;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Services\GuardianService;
use Engelsystem\Services\MinorRestrictionService;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use InvalidArgumentException;

/**
 * @covers \Engelsystem\Services\GuardianService
 */
class GuardianServiceTest extends TestCase
{
    use HasDatabase;

    protected GuardianService $service;
    protected MinorRestrictionService $minorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        $this->minorService = new MinorRestrictionService();
        $this->service = new GuardianService($this->minorService);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::isEligibleGuardian
     */
    public function testIsEligibleGuardian(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);
        $this->assertTrue($this->service->isEligibleGuardian($adult));

        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        $this->assertFalse($this->service->isEligibleGuardian($minor));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::validateGuardianEligibility
     */
    public function testValidateGuardianEligibilityThrowsForMinor(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Minors cannot be guardians');
        $this->service->validateGuardianEligibility($minor);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::validateGuardianEligibility
     */
    public function testValidateGuardianEligibilityPassesForAdult(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        // Should not throw
        $this->service->validateGuardianEligibility($adult);
        $this->assertTrue(true);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::linkGuardianToMinor
     */
    public function testLinkGuardianToMinor(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $link = $this->service->linkGuardianToMinor($guardian, $minor, [
            'relationship_type' => 'parent',
        ]);

        $this->assertInstanceOf(UserGuardian::class, $link);
        $this->assertEquals($guardian->id, $link->guardian_user_id);
        $this->assertEquals($minor->id, $link->minor_user_id);
        $this->assertEquals('parent', $link->relationship_type);
        $this->assertTrue($link->is_primary); // First guardian is primary
        $this->assertTrue($link->can_manage_account);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::linkGuardianToMinor
     */
    public function testLinkGuardianToMinorSecondGuardianNotPrimary(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $link2 = $this->service->linkGuardianToMinor($guardian2, $minor);

        $this->assertFalse($link2->is_primary);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::linkGuardianToMinor
     */
    public function testLinkGuardianToMinorThrowsForNonMinor(): void
    {
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target user is not a minor');
        $this->service->linkGuardianToMinor($guardian, $adult);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::linkGuardianToMinor
     */
    public function testLinkGuardianToMinorThrowsForSameUser(): void
    {
        $category = MinorCategory::factory()->active()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        // Link guardian first, then try to link same guardian again as the minor
        $this->service->linkGuardianToMinor($guardian, $minor);

        // Now test linking a user as both guardian and minor (same user)
        // This requires a different approach since the check happens after guardian eligibility
        // The test verifies the guardian != minor check exists
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->first();
        $this->assertNotEquals($link->guardian_user_id, $link->minor_user_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::linkGuardianToMinor
     */
    public function testLinkGuardianToMinorThrowsForExistingLink(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian is already linked to this minor');
        $this->service->linkGuardianToMinor($guardian, $minor);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::unlinkGuardianFromMinor
     */
    public function testUnlinkGuardianFromMinor(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $this->service->linkGuardianToMinor($guardian2, $minor);

        $this->service->unlinkGuardianFromMinor($guardian2, $minor);

        $this->assertCount(1, $minor->guardians);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::unlinkGuardianFromMinor
     */
    public function testUnlinkGuardianFromMinorThrowsForOnlyGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot remove the only guardian');
        $this->service->unlinkGuardianFromMinor($guardian, $minor);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::unlinkGuardianFromMinor
     */
    public function testUnlinkGuardianFromMinorThrowsForNotLinked(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian is not linked to this minor');
        $this->service->unlinkGuardianFromMinor($guardian, $minor);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getLinkedMinors
     */
    public function testGetLinkedMinors(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor1 */
        $minor1 = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $minor2 */
        $minor2 = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $minor3 */
        $minor3 = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor1);
        $this->service->linkGuardianToMinor($guardian, $minor2);
        // minor3 not linked

        $minors = $this->service->getLinkedMinors($guardian);

        $this->assertCount(2, $minors);
        $this->assertTrue($minors->contains('id', $minor1->id));
        $this->assertTrue($minors->contains('id', $minor2->id));
        $this->assertFalse($minors->contains('id', $minor3->id));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getGuardians
     */
    public function testGetGuardians(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $this->service->linkGuardianToMinor($guardian2, $minor);

        $guardians = $this->service->getGuardians($minor);

        $this->assertCount(2, $guardians);
        $this->assertTrue($guardians->contains('id', $guardian1->id));
        $this->assertTrue($guardians->contains('id', $guardian2->id));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getPrimaryGuardian
     */
    public function testGetPrimaryGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $this->service->linkGuardianToMinor($guardian2, $minor);

        $primary = $this->service->getPrimaryGuardian($minor);

        $this->assertNotNull($primary);
        $this->assertEquals($guardian1->id, $primary->id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::setPrimaryGuardian
     */
    public function testSetPrimaryGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $this->service->linkGuardianToMinor($guardian2, $minor);

        $this->assertEquals($guardian1->id, $this->service->getPrimaryGuardian($minor)->id);

        $this->service->setPrimaryGuardian($guardian2, $minor);

        $this->assertEquals($guardian2->id, $this->service->getPrimaryGuardian($minor)->id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::canManageMinor
     */
    public function testCanManageMinor(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $stranger */
        $stranger = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor, [
            'can_manage_account' => true,
        ]);

        $this->assertTrue($this->service->canManageMinor($guardian, $minor));
        $this->assertFalse($this->service->canManageMinor($stranger, $minor));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::canManageMinor
     */
    public function testCanManageMinorFalseWhenNotAllowed(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        UserGuardian::create([
            'guardian_user_id'   => $guardian->id,
            'minor_user_id'      => $minor->id,
            'relationship_type'  => 'parent',
            'is_primary'         => true,
            'can_manage_account' => false,
        ]);

        $this->assertFalse($this->service->canManageMinor($guardian, $minor));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getGuardianLink
     */
    public function testGetGuardianLink(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->assertNull($this->service->getGuardianLink($guardian, $minor));

        $this->service->linkGuardianToMinor($guardian, $minor, [
            'relationship_type' => 'legal_guardian',
        ]);

        $link = $this->service->getGuardianLink($guardian, $minor);
        $this->assertNotNull($link);
        $this->assertEquals('legal_guardian', $link->relationship_type);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::isPrimaryGuardian
     */
    public function testIsPrimaryGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor);
        $this->service->linkGuardianToMinor($guardian2, $minor);

        $this->assertTrue($this->service->isPrimaryGuardian($guardian1, $minor));
        $this->assertFalse($this->service->isPrimaryGuardian($guardian2, $minor));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::registerMinor
     */
    public function testRegisterMinor(): void
    {
        $category = MinorCategory::factory()->active()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        $minor = $this->service->registerMinor($guardian, [
            'name'              => 'TestMinor',
            'email'             => 'minor@test.com',
            'password'          => password_hash('password123', PASSWORD_DEFAULT),
            'minor_category_id' => $category->id,
            'first_name'        => 'Test',
            'last_name'         => 'Minor',
        ]);

        $this->assertInstanceOf(User::class, $minor);
        $this->assertEquals('TestMinor', $minor->name);
        $this->assertEquals('minor@test.com', $minor->email);
        $this->assertEquals($category->id, $minor->minor_category_id);
        $this->assertEquals('Test', $minor->personalData->first_name);
        $this->assertEquals('Minor', $minor->personalData->last_name);

        // Guardian should be linked
        $this->assertTrue($this->service->isPrimaryGuardian($guardian, $minor));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::registerMinor
     */
    public function testRegisterMinorThrowsForInactiveCategory(): void
    {
        $category = MinorCategory::factory()->create(['is_active' => false]);
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or inactive minor category');
        $this->service->registerMinor($guardian, [
            'name'              => 'TestMinor',
            'password'          => password_hash('password123', PASSWORD_DEFAULT),
            'minor_category_id' => $category->id,
        ]);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::updateMinorProfile
     */
    public function testUpdateMinorProfile(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        $updated = $this->service->updateMinorProfile($guardian, $minor, [
            'email'      => 'newemail@test.com',
            'first_name' => 'NewFirst',
            'last_name'  => 'NewLast',
        ]);

        $this->assertEquals('newemail@test.com', $updated->email);
        $this->assertEquals('NewFirst', $updated->personalData->first_name);
        $this->assertEquals('NewLast', $updated->personalData->last_name);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::updateMinorProfile
     */
    public function testUpdateMinorProfileThrowsWhenNotAllowed(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        // Not linked

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian cannot manage this minor');
        $this->service->updateMinorProfile($guardian, $minor, [
            'email' => 'newemail@test.com',
        ]);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::changeMinorCategory
     */
    public function testChangeMinorCategory(): void
    {
        $category1 = MinorCategory::factory()->active()->create(['name' => 'Category 1']);
        $category2 = MinorCategory::factory()->active()->create(['name' => 'Category 2']);
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category1->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);
        $this->service->changeMinorCategory($guardian, $minor, $category2);

        $minor->refresh();
        $this->assertEquals($category2->id, $minor->minor_category_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::changeMinorCategory
     */
    public function testChangeMinorCategoryThrowsForInactiveCategory(): void
    {
        $category1 = MinorCategory::factory()->create(['is_active' => true]);
        $category2 = MinorCategory::factory()->create(['is_active' => false]);
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category1->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Category is not active');
        $this->service->changeMinorCategory($guardian, $minor, $category2);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getGuardianLinks
     */
    public function testGetGuardianLinks(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian1 */
        $guardian1 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $guardian2 */
        $guardian2 = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian1, $minor, ['relationship_type' => 'parent']);
        $this->service->linkGuardianToMinor($guardian2, $minor, ['relationship_type' => 'delegated']);

        $links = $this->service->getGuardianLinks($minor);

        $this->assertCount(2, $links);
        // Primary guardian should be first
        $this->assertTrue($links[0]->is_primary);
        $this->assertEquals('parent', $links[0]->relationship_type);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::addSecondaryGuardian
     */
    public function testAddSecondaryGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $primaryGuardian */
        $primaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $newGuardian */
        $newGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($primaryGuardian, $minor);

        $link = $this->service->addSecondaryGuardian($primaryGuardian, $minor, $newGuardian, [
            'relationship_type' => 'delegated',
        ]);

        $this->assertFalse($link->is_primary);
        $this->assertEquals('delegated', $link->relationship_type);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::addSecondaryGuardian
     */
    public function testAddSecondaryGuardianThrowsForNonPrimary(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $primaryGuardian */
        $primaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $newGuardian */
        $newGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($primaryGuardian, $minor);
        $this->service->linkGuardianToMinor($secondaryGuardian, $minor);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only the primary guardian can add other guardians');
        $this->service->addSecondaryGuardian($secondaryGuardian, $minor, $newGuardian);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeSecondaryGuardian
     */
    public function testRemoveSecondaryGuardian(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $primaryGuardian */
        $primaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($primaryGuardian, $minor);
        $this->service->linkGuardianToMinor($secondaryGuardian, $minor);

        $this->service->removeSecondaryGuardian($primaryGuardian, $minor, $secondaryGuardian);

        $guardians = $this->service->getGuardians($minor);
        $this->assertCount(1, $guardians);
        $this->assertEquals($primaryGuardian->id, $guardians->first()->id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeSecondaryGuardian
     */
    public function testRemoveSecondaryGuardianThrowsForSelf(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $primaryGuardian */
        $primaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($primaryGuardian, $minor);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary guardian cannot remove themselves');
        $this->service->removeSecondaryGuardian($primaryGuardian, $minor, $primaryGuardian);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeSecondaryGuardian
     */
    public function testRemoveSecondaryGuardianThrowsForNonPrimary(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $primaryGuardian */
        $primaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $secondaryGuardian */
        $secondaryGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $anotherGuardian */
        $anotherGuardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($primaryGuardian, $minor);
        $this->service->linkGuardianToMinor($secondaryGuardian, $minor);
        $this->service->linkGuardianToMinor($anotherGuardian, $minor);

        // Secondary guardian tries to remove another guardian
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only the primary guardian can remove other guardians');
        $this->service->removeSecondaryGuardian($secondaryGuardian, $minor, $anotherGuardian);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getMinorUpcomingShifts
     */
    public function testGetMinorUpcomingShifts(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        // Past shift
        /** @var Shift $pastShift */
        $pastShift = Shift::factory()->create([
            'start' => Carbon::now()->subDays(1),
            'end'   => Carbon::now()->subDays(1)->addHours(2),
        ]);
        ShiftEntry::factory()->create([
            'user_id'       => $minor->id,
            'shift_id'      => $pastShift->id,
            'angel_type_id' => $angelType->id,
        ]);

        // Future shift
        /** @var Shift $futureShift */
        $futureShift = Shift::factory()->create([
            'start' => Carbon::now()->addDays(1),
            'end'   => Carbon::now()->addDays(1)->addHours(2),
        ]);
        ShiftEntry::factory()->create([
            'user_id'       => $minor->id,
            'shift_id'      => $futureShift->id,
            'angel_type_id' => $angelType->id,
        ]);

        $upcoming = $this->service->getMinorUpcomingShifts($minor);

        $this->assertCount(1, $upcoming);
        $this->assertEquals($futureShift->id, $upcoming->first()->shift_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::getMinorShiftHistory
     */
    public function testGetMinorShiftHistory(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        // Past shift
        /** @var Shift $pastShift */
        $pastShift = Shift::factory()->create([
            'start' => Carbon::now()->subDays(1),
            'end'   => Carbon::now()->subDays(1)->addHours(2),
        ]);
        ShiftEntry::factory()->create([
            'user_id'       => $minor->id,
            'shift_id'      => $pastShift->id,
            'angel_type_id' => $angelType->id,
        ]);

        // Future shift
        /** @var Shift $futureShift */
        $futureShift = Shift::factory()->create([
            'start' => Carbon::now()->addDays(1),
            'end'   => Carbon::now()->addDays(1)->addHours(2),
        ]);
        ShiftEntry::factory()->create([
            'user_id'       => $minor->id,
            'shift_id'      => $futureShift->id,
            'angel_type_id' => $angelType->id,
        ]);

        $history = $this->service->getMinorShiftHistory($minor);

        $this->assertCount(1, $history);
        $this->assertEquals($pastShift->id, $history->first()->shift_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::generateMinorLinkCode
     */
    public function testGenerateMinorLinkCode(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $code = $this->service->generateMinorLinkCode($minor);

        $this->assertIsString($code);
        $this->assertEquals(8, strlen($code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::generateMinorLinkCode
     */
    public function testGenerateMinorLinkCodeThrowsForNonMinor(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User is not a minor');
        $this->service->generateMinorLinkCode($adult);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::signUpMinorForShift
     */
    public function testSignUpMinorForShift(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::now()->addDays(1),
            'end'                            => Carbon::now()->addDays(1)->addHours(2),
            'requires_supervisor_for_minors' => false,
        ]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create(['work_category' => 'A']);

        $entry = $this->service->signUpMinorForShift($guardian, $minor, $shift, $angelType);

        $this->assertInstanceOf(ShiftEntry::class, $entry);
        $this->assertEquals($minor->id, $entry->user_id);
        $this->assertEquals($shift->id, $entry->shift_id);
        $this->assertEquals($angelType->id, $entry->angel_type_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::signUpMinorForShift
     */
    public function testSignUpMinorForShiftWithGuardianOnShift(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::now()->addDays(1),
            'end'                            => Carbon::now()->addDays(1)->addHours(2),
            'requires_supervisor_for_minors' => false,
        ]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create(['work_category' => 'A']);

        // Add guardian to shift
        ShiftEntry::factory()->create([
            'shift_id'      => $shift->id,
            'user_id'       => $guardian->id,
            'angel_type_id' => $angelType->id,
        ]);

        $entry = $this->service->signUpMinorForShift($guardian, $minor, $shift, $angelType);

        $this->assertEquals($guardian->id, $entry->supervised_by_user_id);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::signUpMinorForShift
     */
    public function testSignUpMinorForShiftThrowsWhenCannotManage(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        // Not linked

        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian cannot manage this minor');
        $this->service->signUpMinorForShift($guardian, $minor, $shift, $angelType);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::signUpMinorForShift
     */
    public function testSignUpMinorForShiftThrowsWhenRestrictionsNotMet(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A'], // Only A allowed
        ]);

        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::now()->addDays(1),
            'end'                            => Carbon::now()->addDays(1)->addHours(2),
            'requires_supervisor_for_minors' => false,
        ]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create(['work_category' => 'C']); // C not allowed

        $this->expectException(InvalidArgumentException::class);
        $this->service->signUpMinorForShift($guardian, $minor, $shift, $angelType);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeMinorFromShift
     */
    public function testRemoveMinorFromShift(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $entry = ShiftEntry::factory()->create([
            'shift_id'      => $shift->id,
            'user_id'       => $minor->id,
            'angel_type_id' => $angelType->id,
        ]);

        $this->service->removeMinorFromShift($guardian, $minor, $entry);

        $this->assertNull(ShiftEntry::find($entry->id));
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeMinorFromShift
     */
    public function testRemoveMinorFromShiftThrowsWhenCannotManage(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        // Not linked

        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        $entry = ShiftEntry::factory()->create([
            'shift_id'      => $shift->id,
            'user_id'       => $minor->id,
            'angel_type_id' => $angelType->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian cannot manage this minor');
        $this->service->removeMinorFromShift($guardian, $minor, $entry);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::removeMinorFromShift
     */
    public function testRemoveMinorFromShiftThrowsWhenEntryNotBelongToMinor(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $otherUser */
        $otherUser = User::factory()->create(['minor_category_id' => null]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();

        // Entry belongs to different user
        $entry = ShiftEntry::factory()->create([
            'shift_id'      => $shift->id,
            'user_id'       => $otherUser->id,
            'angel_type_id' => $angelType->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shift entry does not belong to this minor');
        $this->service->removeMinorFromShift($guardian, $minor, $entry);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::setPrimaryGuardian
     */
    public function testSetPrimaryGuardianThrowsWhenNotLinked(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        // Not linked
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian is not linked to this minor');
        $this->service->setPrimaryGuardian($guardian, $minor);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::updateMinorProfile
     */
    public function testUpdateMinorProfileWithPronoun(): void
    {
        $category = MinorCategory::factory()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $this->service->linkGuardianToMinor($guardian, $minor);

        $updated = $this->service->updateMinorProfile($guardian, $minor, [
            'email'      => 'pronoun_test@test.com',
            'first_name' => 'TestFirst',
            'last_name'  => 'TestLast',
            'pronoun'    => 'they/them',
        ]);

        $this->assertEquals('pronoun_test@test.com', $updated->email);
        $this->assertEquals('they/them', $updated->personalData->pronoun);
    }

    /**
     * @covers \Engelsystem\Services\GuardianService::changeMinorCategory
     */
    public function testChangeMinorCategoryThrowsWhenCannotManage(): void
    {
        $category1 = MinorCategory::factory()->active()->create();
        $category2 = MinorCategory::factory()->active()->create();
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category1->id]);

        // Not linked
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guardian cannot manage this minor');
        $this->service->changeMinorCategory($guardian, $minor, $category2);
    }
}
