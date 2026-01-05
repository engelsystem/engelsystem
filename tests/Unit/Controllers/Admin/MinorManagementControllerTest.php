<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Controllers\Admin\MinorManagementController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Services\MinorRestrictionService;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * @covers \Engelsystem\Controllers\Admin\MinorManagementController
 */
class MinorManagementControllerTest extends TestCase
{
    use HasDatabase;

    protected MinorRestrictionService $minorService;
    protected Response $response;
    protected Authenticator $auth;
    protected MinorManagementController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();
        $this->mockTranslator();
        $this->app->instance('config', new Config([]));

        $this->minorService = new MinorRestrictionService();
        $this->response = $this->createMock(Response::class);
        $this->auth = $this->createMock(Authenticator::class);

        $this->controller = new MinorManagementController(
            $this->response,
            $this->auth,
            $this->minorService
        );
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::__construct
     */
    public function testIndexEmpty(): void
    {
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/minors/index.twig', $view);
                $this->assertArrayHasKey('minors', $data);
                $this->assertCount(0, $data['minors']);
                $this->assertArrayHasKey('search', $data);
                $this->assertArrayHasKey('categories', $data);
                $this->assertArrayHasKey('supervisionGaps', $data);
                $this->assertArrayHasKey('categoryStats', $data);
                $this->assertArrayHasKey('consentStats', $data);
                $this->assertEquals(0, $data['consentStats']['approved']);
                $this->assertEquals(0, $data['consentStats']['pending']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithMinors(): void
    {
        $category = MinorCategory::factory()->create([
            'name' => 'Junior Angel',
            'max_hours_per_day' => 2,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'minor1',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => null,
        ]);

        User::factory()->create([
            'name' => 'minor2',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => User::factory()->create()->id,
            'consent_approved_at' => Carbon::now(),
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/minors/index.twig', $view);
                $this->assertCount(2, $data['minors']);
                $this->assertEquals(1, $data['consentStats']['approved']);
                $this->assertEquals(1, $data['consentStats']['pending']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithSearchFilter(): void
    {
        $category = MinorCategory::factory()->create(['is_active' => true]);

        User::factory()->create([
            'name' => 'alice_minor',
            'minor_category_id' => $category->id,
        ]);

        User::factory()->create([
            'name' => 'bob_minor',
            'minor_category_id' => $category->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['minors']);
                $this->assertEquals('alice', $data['search']);
                return $this->response;
            });

        $request = Request::create('/', 'POST', ['search' => 'alice']);
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithCategoryFilter(): void
    {
        $juniorCategory = MinorCategory::factory()->create([
            'name' => 'Junior',
            'is_active' => true,
        ]);
        $teenCategory = MinorCategory::factory()->create([
            'name' => 'Teen',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'junior_minor',
            'minor_category_id' => $juniorCategory->id,
        ]);

        User::factory()->create([
            'name' => 'teen_minor',
            'minor_category_id' => $teenCategory->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($juniorCategory) {
                $this->assertCount(1, $data['minors']);
                $this->assertEquals($juniorCategory->id, $data['category']);
                return $this->response;
            });

        $request = Request::create('/', 'POST', ['category' => (string) $juniorCategory->id]);
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithConsentFilterApproved(): void
    {
        $category = MinorCategory::factory()->create(['is_active' => true]);
        $approver = User::factory()->create();

        User::factory()->create([
            'name' => 'approved_minor',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => $approver->id,
        ]);

        User::factory()->create([
            'name' => 'pending_minor',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => null,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['minors']);
                $this->assertEquals('approved', $data['consent']);
                return $this->response;
            });

        $request = Request::create('/', 'POST', ['consent' => 'approved']);
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithConsentFilterPending(): void
    {
        $category = MinorCategory::factory()->create(['is_active' => true]);
        $approver = User::factory()->create();

        User::factory()->create([
            'name' => 'approved_minor',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => $approver->id,
        ]);

        User::factory()->create([
            'name' => 'pending_minor',
            'minor_category_id' => $category->id,
            'consent_approved_by_user_id' => null,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['minors']);
                $this->assertEquals('pending', $data['consent']);
                return $this->response;
            });

        $request = Request::create('/', 'POST', ['consent' => 'pending']);
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexWithGuardians(): void
    {
        $category = MinorCategory::factory()->create(['is_active' => true]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'name' => 'minor_with_guardian',
            'minor_category_id' => $category->id,
        ]);

        /** @var User $guardian */
        $guardian = User::factory()->create(['name' => 'guardian_parent']);

        UserGuardian::factory()->create([
            'minor_user_id' => $minor->id,
            'guardian_user_id' => $guardian->id,
            'is_primary' => true,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['minors']);
                $minorData = $data['minors'][0];
                $this->assertCount(1, $minorData['user']->guardians);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexCalculatesDailyHours(): void
    {
        $category = MinorCategory::factory()->create([
            'max_hours_per_day' => 4,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'name' => 'working_minor',
            'minor_category_id' => $category->id,
        ]);

        // Create a shift for today
        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();
        $today = Carbon::today();

        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => $today->copy()->setHour(10),
            'end' => $today->copy()->setHour(12),
        ]);

        ShiftEntry::factory()->create([
            'shift_id' => $shift->id,
            'user_id' => $minor->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['minors']);
                $minorData = $data['minors'][0];
                $this->assertEquals(2.0, $minorData['hoursUsed']);
                $this->assertEquals(4, $minorData['maxHours']);
                $this->assertEquals(50, $minorData['hoursPercent']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::getSupervisionGaps
     */
    public function testIndexDetectsSupervisionGaps(): void
    {
        $category = MinorCategory::factory()->create([
            'requires_supervisor' => true,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'name' => 'unsupervised_minor',
            'minor_category_id' => $category->id,
        ]);

        // Create a future shift that requires supervision
        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();

        $futureShift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => Carbon::now()->addHours(2),
            'end' => Carbon::now()->addHours(4),
            'requires_supervisor_for_minors' => true,
        ]);

        ShiftEntry::factory()->create([
            'shift_id' => $futureShift->id,
            'user_id' => $minor->id,
            'supervised_by_user_id' => null,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(1, $data['supervisionGaps']);
                $gap = $data['supervisionGaps'][0];
                $this->assertArrayHasKey('shift', $gap);
                $this->assertArrayHasKey('minors', $gap);
                $this->assertCount(1, $gap['minors']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::getSupervisionGaps
     */
    public function testIndexNoGapWhenSupervisorAssigned(): void
    {
        $category = MinorCategory::factory()->create([
            'requires_supervisor' => true,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'name' => 'supervised_minor',
            'minor_category_id' => $category->id,
        ]);

        /** @var User $supervisor */
        $supervisor = User::factory()->create(['name' => 'supervisor']);

        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();

        $futureShift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => Carbon::now()->addHours(2),
            'end' => Carbon::now()->addHours(4),
            'requires_supervisor_for_minors' => true,
        ]);

        ShiftEntry::factory()->create([
            'shift_id' => $futureShift->id,
            'user_id' => $minor->id,
            'supervised_by_user_id' => $supervisor->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(0, $data['supervisionGaps']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::getCategoryStatistics
     */
    public function testIndexCalculatesCategoryStatistics(): void
    {
        $juniorCategory = MinorCategory::factory()->create([
            'name' => 'Junior',
            'is_active' => true,
            'display_order' => 1,
        ]);
        $teenCategory = MinorCategory::factory()->create([
            'name' => 'Teen',
            'is_active' => true,
            'display_order' => 2,
        ]);

        User::factory()->count(2)->create(['minor_category_id' => $juniorCategory->id]);
        User::factory()->count(3)->create(['minor_category_id' => $teenCategory->id]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertArrayHasKey('categoryStats', $data);
                $this->assertEquals(2, $data['categoryStats']['Junior']);
                $this->assertEquals(3, $data['categoryStats']['Teen']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexIgnoresPastShiftsForSupervisionGaps(): void
    {
        $category = MinorCategory::factory()->create([
            'requires_supervisor' => true,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id' => $category->id,
        ]);

        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();

        // Create a past shift - should not appear in gaps
        $pastShift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => Carbon::now()->subHours(4),
            'end' => Carbon::now()->subHours(2),
            'requires_supervisor_for_minors' => true,
        ]);

        ShiftEntry::factory()->create([
            'shift_id' => $pastShift->id,
            'user_id' => $minor->id,
            'supervised_by_user_id' => null,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertCount(0, $data['supervisionGaps']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexHoursPercentCappedAt100(): void
    {
        $category = MinorCategory::factory()->create([
            'max_hours_per_day' => 2,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'minor_category_id' => $category->id,
        ]);

        // Create shifts totaling more than max hours
        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();
        $today = Carbon::today();

        $shift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => $today->copy()->setHour(8),
            'end' => $today->copy()->setHour(12), // 4 hours - over the 2 hour limit
        ]);

        ShiftEntry::factory()->create([
            'shift_id' => $shift->id,
            'user_id' => $minor->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $minorData = $data['minors'][0];
                $this->assertEquals(100, $minorData['hoursPercent']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     */
    public function testIndexMinorWithNoHoursLimit(): void
    {
        $category = MinorCategory::factory()->create([
            'max_hours_per_day' => null,
            'is_active' => true,
        ]);

        User::factory()->create([
            'minor_category_id' => $category->id,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $minorData = $data['minors'][0];
                $this->assertNull($minorData['maxHours']);
                $this->assertEquals(0, $minorData['hoursPercent']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::index
     * @covers \Engelsystem\Controllers\Admin\MinorManagementController::getSupervisionGaps
     */
    public function testIndexSupervisionGapsIgnoresNonMinorUsers(): void
    {
        $category = MinorCategory::factory()->create([
            'requires_supervisor' => true,
            'is_active' => true,
        ]);

        /** @var User $minor */
        $minor = User::factory()->create([
            'name' => 'minor_user',
            'minor_category_id' => $category->id,
        ]);

        /** @var User $adult */
        $adult = User::factory()->create([
            'name' => 'adult_user',
            'minor_category_id' => null,
        ]);

        $shiftType = ShiftType::factory()->create();
        $location = Location::factory()->create();

        $futureShift = Shift::factory()->create([
            'shift_type_id' => $shiftType->id,
            'location_id' => $location->id,
            'start' => Carbon::now()->addHours(2),
            'end' => Carbon::now()->addHours(4),
            'requires_supervisor_for_minors' => true,
        ]);

        // Adult user without supervision - should not be a gap
        ShiftEntry::factory()->create([
            'shift_id' => $futureShift->id,
            'user_id' => $adult->id,
            'supervised_by_user_id' => null,
        ]);

        // Minor without supervision - should be a gap
        ShiftEntry::factory()->create([
            'shift_id' => $futureShift->id,
            'user_id' => $minor->id,
            'supervised_by_user_id' => null,
        ]);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                // Should have 1 gap with 1 minor (not 2 users)
                $this->assertCount(1, $data['supervisionGaps']);
                $gap = $data['supervisionGaps'][0];
                $this->assertCount(1, $gap['minors']);
                return $this->response;
            });

        $request = Request::create('/');
        $this->controller->index($request);
    }
}
