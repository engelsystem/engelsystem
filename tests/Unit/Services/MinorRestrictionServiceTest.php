<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Services;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Models\UserSupervisorStatus;
use Engelsystem\Services\MinorRestrictionService;
use Engelsystem\Services\MinorRestrictions;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * @covers \Engelsystem\Services\MinorRestrictionService
 */
class MinorRestrictionServiceTest extends TestCase
{
    use HasDatabase;

    protected MinorRestrictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        $this->service = new MinorRestrictionService();
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getCategory
     */
    public function testGetCategoryReturnsNullForAdult(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $this->assertNull($this->service->getCategory($adult));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getCategory
     */
    public function testGetCategoryReturnsMinorCategory(): void
    {
        $category = MinorCategory::factory()->create(['name' => 'Junior Angel']);

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $result = $this->service->getCategory($minor);

        $this->assertNotNull($result);
        $this->assertEquals('Junior Angel', $result->name);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::isMinor
     */
    public function testIsMinor(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);
        $this->assertFalse($this->service->isMinor($adult));

        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        $this->assertTrue($this->service->isMinor($minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkAngelType
     */
    public function testCanWorkAngelTypeForAdult(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        /** @var AngelType $angelTypeA */
        $angelTypeA = AngelType::factory()->create(['work_category' => 'A']);
        /** @var AngelType $angelTypeB */
        $angelTypeB = AngelType::factory()->create(['work_category' => 'B']);
        /** @var AngelType $angelTypeC */
        $angelTypeC = AngelType::factory()->create(['work_category' => 'C']);

        $this->assertTrue($this->service->canWorkAngelType($adult, $angelTypeA));
        $this->assertTrue($this->service->canWorkAngelType($adult, $angelTypeB));
        $this->assertTrue($this->service->canWorkAngelType($adult, $angelTypeC));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkAngelType
     */
    public function testCanWorkAngelTypeForMinor(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A'],
        ]);

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var AngelType $angelTypeA */
        $angelTypeA = AngelType::factory()->create(['work_category' => 'A']);
        /** @var AngelType $angelTypeB */
        $angelTypeB = AngelType::factory()->create(['work_category' => 'B']);
        /** @var AngelType $angelTypeC */
        $angelTypeC = AngelType::factory()->create(['work_category' => 'C']);
        /** @var AngelType $angelTypeNull */
        $angelTypeNull = AngelType::factory()->create(['work_category' => null]);

        $this->assertTrue($this->service->canWorkAngelType($minor, $angelTypeA));
        $this->assertFalse($this->service->canWorkAngelType($minor, $angelTypeB));
        $this->assertFalse($this->service->canWorkAngelType($minor, $angelTypeC));
        // Null work_category defaults to 'C'
        $this->assertFalse($this->service->canWorkAngelType($minor, $angelTypeNull));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getRestrictions
     */
    public function testGetRestrictionsForAdult(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $restrictions = $this->service->getRestrictions($adult);

        $this->assertInstanceOf(MinorRestrictions::class, $restrictions);
        $this->assertNull($restrictions->minShiftStartHour);
        $this->assertNull($restrictions->maxShiftEndHour);
        $this->assertNull($restrictions->maxHoursPerDay);
        $this->assertEquals(['A', 'B', 'C'], $restrictions->allowedWorkCategories);
        $this->assertTrue($restrictions->canFillSlot);
        $this->assertFalse($restrictions->requiresSupervisor);
        $this->assertTrue($restrictions->canSelfSignup);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getRestrictions
     */
    public function testGetRestrictionsForMinor(): void
    {
        $category = MinorCategory::factory()->create([
            'min_shift_start_hour'    => 8,
            'max_shift_end_hour'      => 18,
            'max_hours_per_day'       => 2,
            'allowed_work_categories' => ['A'],
            'can_fill_slot'           => true,
            'requires_supervisor'     => true,
            'can_self_signup'         => false,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        $restrictions = $this->service->getRestrictions($minor);

        $this->assertEquals(8, $restrictions->minShiftStartHour);
        $this->assertEquals(18, $restrictions->maxShiftEndHour);
        $this->assertEquals(2, $restrictions->maxHoursPerDay);
        $this->assertEquals(['A'], $restrictions->allowedWorkCategories);
        $this->assertTrue($restrictions->canFillSlot);
        $this->assertTrue($restrictions->requiresSupervisor);
        $this->assertFalse($restrictions->canSelfSignup);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftAdultAlwaysSucceeds(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 02:00:00'),
            'end'   => Carbon::parse('2026-01-01 06:00:00'),
        ]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create(['work_category' => 'C']);

        $result = $this->service->canWorkShift($adult, $shift, $angelType);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorNoConsent(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => null,
            'consent_approved_at'         => null,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 10:00:00'),
            'end'                            => Carbon::parse('2026-01-01 12:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Consent', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorWrongWorkCategory(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 10:00:00'),
            'end'                            => Carbon::parse('2026-01-01 12:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create([
            'name'          => 'Bar Service',
            'work_category' => 'C',
        ]);

        $result = $this->service->canWorkShift($minor, $shift, $angelType);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Bar Service', $result->errors[0]);
        $this->assertStringContainsString('category C', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorTooEarly(): void
    {
        $category = MinorCategory::factory()->create([
            'min_shift_start_hour'    => 8,
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 06:00:00'),
            'end'                            => Carbon::parse('2026-01-01 10:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('06:00', $result->errors[0]);
        $this->assertStringContainsString('08:00', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorTooLate(): void
    {
        $category = MinorCategory::factory()->create([
            'max_shift_end_hour'      => 18,
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 16:00:00'),
            'end'                            => Carbon::parse('2026-01-01 20:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('20:00', $result->errors[0]);
        $this->assertStringContainsString('18:00', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorExactEndTimeAllowed(): void
    {
        $category = MinorCategory::factory()->create([
            'max_shift_end_hour'      => 18,
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 16:00:00'),
            'end'                            => Carbon::parse('2026-01-01 18:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertTrue($result->isValid);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     * @covers \Engelsystem\Services\MinorRestrictionService::getDailyHoursRemaining
     */
    public function testCanWorkShiftMinorExceedsDailyHours(): void
    {
        $category = MinorCategory::factory()->create([
            'max_hours_per_day'       => 2,
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        // Already has a 1.5 hour shift
        /** @var Shift $existingShift */
        $existingShift = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 10:00:00'),
            'end'   => Carbon::parse('2026-01-01 11:30:00'),
        ]);
        ShiftEntry::factory()->create([
            'user_id'  => $minor->id,
            'shift_id' => $existingShift->id,
        ]);

        // Try to sign up for a 1 hour shift (would total 2.5 hours)
        /** @var Shift $newShift */
        $newShift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 14:00:00'),
            'end'                            => Carbon::parse('2026-01-01 15:00:00'),
            'requires_supervisor_for_minors' => false,
        ]);

        $result = $this->service->canWorkShift($minor, $newShift);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('1.0 hours', $result->errors[0]);
        $this->assertStringContainsString('0.5 remaining', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     */
    public function testCanWorkShiftMinorNoSupervisor(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => true,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 10:00:00'),
            'end'                            => Carbon::parse('2026-01-01 12:00:00'),
            'requires_supervisor_for_minors' => true,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('supervisor', $result->errors[0]);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     * @covers \Engelsystem\Services\MinorRestrictionService::isWillingSupervisor
     */
    public function testCanWorkShiftMinorWithSupervisor(): void
    {
        $category = MinorCategory::factory()->create([
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => true,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var User $supervisor */
        $supervisor = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $supervisor->id]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 10:00:00'),
            'end'                            => Carbon::parse('2026-01-01 12:00:00'),
            'requires_supervisor_for_minors' => true,
        ]);

        // Supervisor is on the shift
        ShiftEntry::factory()->create([
            'user_id'  => $supervisor->id,
            'shift_id' => $shift->id,
        ]);

        $result = $this->service->canWorkShift($minor, $shift);

        $this->assertTrue($result->isValid);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     * @covers \Engelsystem\Services\MinorRestrictionService::isGuardianOf
     */
    public function testShiftHasWillingSupervisorWithGuardian(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        // Guardian is not a willing supervisor in general, but is the minor's guardian
        UserGuardian::factory()->create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian->id,
        ]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Guardian is on the shift
        ShiftEntry::factory()->create([
            'user_id'  => $guardian->id,
            'shift_id' => $shift->id,
        ]);

        $this->assertTrue($this->service->shiftHasWillingSupervisor($shift, $minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::isWillingSupervisor
     */
    public function testIsWillingSupervisor(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $user1->id]);
        $this->assertTrue($this->service->isWillingSupervisor($user1));

        /** @var User $user2 */
        $user2 = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->notWilling()->create(['user_id' => $user2->id]);
        $this->assertFalse($this->service->isWillingSupervisor($user2));

        /** @var User $user3 */
        $user3 = User::factory()->create(['minor_category_id' => null]);
        // No supervisor status record
        $this->assertFalse($this->service->isWillingSupervisor($user3));

        // Minors cannot be supervisors
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $minor->id]);
        $this->assertFalse($this->service->isWillingSupervisor($minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::isGuardianOf
     */
    public function testIsGuardianOf(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);
        /** @var User $stranger */
        $stranger = User::factory()->create(['minor_category_id' => null]);

        UserGuardian::factory()->create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian->id,
        ]);

        $this->assertTrue($this->service->isGuardianOf($guardian, $minor));
        $this->assertFalse($this->service->isGuardianOf($stranger, $minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::isGuardianOf
     */
    public function testIsGuardianOfExpiredGuardianship(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $guardian */
        $guardian = User::factory()->create(['minor_category_id' => null]);

        UserGuardian::factory()->expired()->create([
            'minor_user_id'    => $minor->id,
            'guardian_user_id' => $guardian->id,
        ]);

        $this->assertFalse($this->service->isGuardianOf($guardian, $minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getDailyHoursUsed
     */
    public function testGetDailyHoursUsed(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // No shifts - should be 0
        $this->assertEquals(0.0, $this->service->getDailyHoursUsed($user, Carbon::parse('2026-01-01')));

        // Add a 2-hour shift
        /** @var Shift $shift1 */
        $shift1 = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 10:00:00'),
            'end'   => Carbon::parse('2026-01-01 12:00:00'),
        ]);
        ShiftEntry::factory()->create(['user_id' => $user->id, 'shift_id' => $shift1->id]);

        $this->assertEquals(2.0, $this->service->getDailyHoursUsed($user, Carbon::parse('2026-01-01')));

        // Add another 1.5-hour shift on the same day
        /** @var Shift $shift2 */
        $shift2 = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 14:00:00'),
            'end'   => Carbon::parse('2026-01-01 15:30:00'),
        ]);
        ShiftEntry::factory()->create(['user_id' => $user->id, 'shift_id' => $shift2->id]);

        $this->assertEquals(3.5, $this->service->getDailyHoursUsed($user, Carbon::parse('2026-01-01')));

        // Different day should be 0
        $this->assertEquals(0.0, $this->service->getDailyHoursUsed($user, Carbon::parse('2026-01-02')));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getShiftDurationHours
     */
    public function testGetShiftDurationHours(): void
    {
        /** @var Shift $shift1 */
        $shift1 = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 10:00:00'),
            'end'   => Carbon::parse('2026-01-01 12:00:00'),
        ]);
        $this->assertEquals(2.0, $this->service->getShiftDurationHours($shift1));

        /** @var Shift $shift2 */
        $shift2 = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 10:00:00'),
            'end'   => Carbon::parse('2026-01-01 11:30:00'),
        ]);
        $this->assertEquals(1.5, $this->service->getShiftDurationHours($shift2));

        /** @var Shift $shift3 */
        $shift3 = Shift::factory()->create([
            'start' => Carbon::parse('2026-01-01 22:00:00'),
            'end'   => Carbon::parse('2026-01-02 02:00:00'),
        ]);
        $this->assertEquals(4.0, $this->service->getShiftDurationHours($shift3));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getAvailableSupervisorsForShift
     */
    public function testGetAvailableSupervisorsForShift(): void
    {
        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // No one on shift
        $this->assertEmpty($this->service->getAvailableSupervisorsForShift($shift));

        // Add a willing supervisor
        /** @var User $supervisor */
        $supervisor = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $supervisor->id]);
        ShiftEntry::factory()->create(['user_id' => $supervisor->id, 'shift_id' => $shift->id]);

        $supervisors = $this->service->getAvailableSupervisorsForShift($shift);
        $this->assertCount(1, $supervisors);
        $this->assertEquals($supervisor->id, $supervisors[0]->id);

        // Add a non-willing adult
        /** @var User $nonWilling */
        $nonWilling = User::factory()->create(['minor_category_id' => null]);
        ShiftEntry::factory()->create(['user_id' => $nonWilling->id, 'shift_id' => $shift->id]);

        $supervisors = $this->service->getAvailableSupervisorsForShift($shift);
        $this->assertCount(1, $supervisors);

        // Add a minor (should not be counted)
        $category = MinorCategory::factory()->create();
        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $minor->id]);
        ShiftEntry::factory()->create(['user_id' => $minor->id, 'shift_id' => $shift->id]);

        $supervisors = $this->service->getAvailableSupervisorsForShift($shift);
        $this->assertCount(1, $supervisors);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canSelfSignup
     */
    public function testCanSelfSignup(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);
        $this->assertTrue($this->service->canSelfSignup($adult));

        $categoryCanSignup = MinorCategory::factory()->create(['can_self_signup' => true]);
        /** @var User $minor1 */
        $minor1 = User::factory()->create(['minor_category_id' => $categoryCanSignup->id]);
        $this->assertTrue($this->service->canSelfSignup($minor1));

        $categoryCannotSignup = MinorCategory::factory()->create(['can_self_signup' => false]);
        /** @var User $minor2 */
        $minor2 = User::factory()->create(['minor_category_id' => $categoryCannotSignup->id]);
        $this->assertFalse($this->service->canSelfSignup($minor2));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::countsTowardQuota
     */
    public function testCountsTowardQuota(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);
        $this->assertTrue($this->service->countsTowardQuota($adult));

        $categoryCanFill = MinorCategory::factory()->create(['can_fill_slot' => true]);
        /** @var User $minor1 */
        $minor1 = User::factory()->create(['minor_category_id' => $categoryCanFill->id]);
        $this->assertTrue($this->service->countsTowardQuota($minor1));

        $categoryCannotFill = MinorCategory::factory()->create(['can_fill_slot' => false]);
        /** @var User $minor2 */
        $minor2 = User::factory()->create(['minor_category_id' => $categoryCannotFill->id]);
        $this->assertFalse($this->service->countsTowardQuota($minor2));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::getDailyHoursRemaining
     */
    public function testGetDailyHoursRemainingForAdultReturnsMaxFloat(): void
    {
        /** @var User $adult */
        $adult = User::factory()->create(['minor_category_id' => null]);

        $remaining = $this->service->getDailyHoursRemaining($adult, Carbon::parse('2026-01-01'));

        // Adults have no limit, so remaining should be PHP_FLOAT_MAX
        $this->assertEquals(PHP_FLOAT_MAX, $remaining);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     */
    public function testShiftHasWillingSupervisorSkipsMinorThemselves(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var User $supervisor */
        $supervisor = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $supervisor->id]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Minor is already signed up for the shift
        ShiftEntry::factory()->create(['user_id' => $minor->id, 'shift_id' => $shift->id]);
        // Supervisor is also on the shift
        ShiftEntry::factory()->create(['user_id' => $supervisor->id, 'shift_id' => $shift->id]);

        // Should still find the supervisor (skipping the minor themselves)
        $this->assertTrue($this->service->shiftHasWillingSupervisor($shift, $minor));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     */
    public function testShiftHasWillingSupervisorSkipsOtherMinors(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor1 */
        $minor1 = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $minor2 */
        $minor2 = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Another minor is on the shift (should be skipped as potential supervisor)
        ShiftEntry::factory()->create(['user_id' => $minor2->id, 'shift_id' => $shift->id]);

        // No supervisor on the shift - should return false because the other minor can't supervise
        $this->assertFalse($this->service->shiftHasWillingSupervisor($shift, $minor1));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::shiftHasWillingSupervisor
     */
    public function testShiftHasWillingSupervisorWithMinorOnShiftAndSupervisor(): void
    {
        $category = MinorCategory::factory()->create();

        /** @var User $minor1 */
        $minor1 = User::factory()->create(['minor_category_id' => $category->id]);
        /** @var User $minor2 */
        $minor2 = User::factory()->create(['minor_category_id' => $category->id]);

        /** @var User $supervisor */
        $supervisor = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $supervisor->id]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // The minor themselves is on the shift
        ShiftEntry::factory()->create(['user_id' => $minor1->id, 'shift_id' => $shift->id]);
        // Another minor is on the shift (should be skipped)
        ShiftEntry::factory()->create(['user_id' => $minor2->id, 'shift_id' => $shift->id]);
        // A supervisor is also on the shift
        ShiftEntry::factory()->create(['user_id' => $supervisor->id, 'shift_id' => $shift->id]);

        // Should find the supervisor (after skipping both minors)
        $this->assertTrue($this->service->shiftHasWillingSupervisor($shift, $minor1));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictionService::canWorkShift
     */
    public function testCanWorkShiftMinorAllValidationsPass(): void
    {
        $category = MinorCategory::factory()->create([
            'min_shift_start_hour'    => 8,
            'max_shift_end_hour'      => 18,
            'max_hours_per_day'       => 4,
            'allowed_work_categories' => ['A', 'B'],
            'requires_supervisor'     => true,
        ]);

        /** @var User $approver */
        $approver = User::factory()->create();

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id'           => $category->id,
            'consent_approved_by_user_id' => $approver->id,
            'consent_approved_at'         => Carbon::now(),
        ]);

        /** @var User $supervisor */
        $supervisor = User::factory()->create(['minor_category_id' => null]);
        UserSupervisorStatus::factory()->willing()->create(['user_id' => $supervisor->id]);

        /** @var Shift $shift */
        $shift = Shift::factory()->create([
            'start'                          => Carbon::parse('2026-01-01 10:00:00'),
            'end'                            => Carbon::parse('2026-01-01 12:00:00'),
            'requires_supervisor_for_minors' => true,
        ]);

        ShiftEntry::factory()->create(['user_id' => $supervisor->id, 'shift_id' => $shift->id]);

        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create(['work_category' => 'A']);

        $result = $this->service->canWorkShift($minor, $shift, $angelType);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }
}
