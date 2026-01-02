<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Services;

use Engelsystem\Services\MinorRestrictions;
use Engelsystem\Test\Unit\TestCase;

class MinorRestrictionsTest extends TestCase
{
    /**
     * @covers \Engelsystem\Services\MinorRestrictions::__construct
     */
    public function testConstruct(): void
    {
        $restrictions = new MinorRestrictions(
            minShiftStartHour: 8,
            maxShiftEndHour: 18,
            maxHoursPerDay: 2,
            allowedWorkCategories: ['A'],
            canFillSlot: true,
            requiresSupervisor: true,
            canSelfSignup: false
        );

        $this->assertEquals(8, $restrictions->minShiftStartHour);
        $this->assertEquals(18, $restrictions->maxShiftEndHour);
        $this->assertEquals(2, $restrictions->maxHoursPerDay);
        $this->assertEquals(['A'], $restrictions->allowedWorkCategories);
        $this->assertTrue($restrictions->canFillSlot);
        $this->assertTrue($restrictions->requiresSupervisor);
        $this->assertFalse($restrictions->canSelfSignup);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictions::forAdult
     */
    public function testForAdult(): void
    {
        $restrictions = MinorRestrictions::forAdult();

        $this->assertNull($restrictions->minShiftStartHour);
        $this->assertNull($restrictions->maxShiftEndHour);
        $this->assertNull($restrictions->maxHoursPerDay);
        $this->assertEquals(['A', 'B', 'C'], $restrictions->allowedWorkCategories);
        $this->assertTrue($restrictions->canFillSlot);
        $this->assertFalse($restrictions->requiresSupervisor);
        $this->assertTrue($restrictions->canSelfSignup);
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictions::allowsWorkCategory
     */
    public function testAllowsWorkCategory(): void
    {
        $restrictedRestrictions = new MinorRestrictions(
            minShiftStartHour: 8,
            maxShiftEndHour: 18,
            maxHoursPerDay: 2,
            allowedWorkCategories: ['A'],
            canFillSlot: true,
            requiresSupervisor: true,
            canSelfSignup: true
        );

        $this->assertTrue($restrictedRestrictions->allowsWorkCategory('A'));
        $this->assertFalse($restrictedRestrictions->allowsWorkCategory('B'));
        $this->assertFalse($restrictedRestrictions->allowsWorkCategory('C'));

        $mediumRestrictions = new MinorRestrictions(
            minShiftStartHour: 6,
            maxShiftEndHour: 20,
            maxHoursPerDay: 8,
            allowedWorkCategories: ['A', 'B'],
            canFillSlot: true,
            requiresSupervisor: true,
            canSelfSignup: true
        );

        $this->assertTrue($mediumRestrictions->allowsWorkCategory('A'));
        $this->assertTrue($mediumRestrictions->allowsWorkCategory('B'));
        $this->assertFalse($mediumRestrictions->allowsWorkCategory('C'));

        $adultRestrictions = MinorRestrictions::forAdult();
        $this->assertTrue($adultRestrictions->allowsWorkCategory('A'));
        $this->assertTrue($adultRestrictions->allowsWorkCategory('B'));
        $this->assertTrue($adultRestrictions->allowsWorkCategory('C'));
    }

    /**
     * @covers \Engelsystem\Services\MinorRestrictions::hasRestrictions
     */
    public function testHasRestrictions(): void
    {
        // Adult has no restrictions
        $adultRestrictions = MinorRestrictions::forAdult();
        $this->assertFalse($adultRestrictions->hasRestrictions());

        // Minor with time restrictions
        $timeRestricted = new MinorRestrictions(
            minShiftStartHour: 8,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A', 'B', 'C'],
            canFillSlot: true,
            requiresSupervisor: false,
            canSelfSignup: true
        );
        $this->assertTrue($timeRestricted->hasRestrictions());

        // Minor with work category restrictions
        $categoryRestricted = new MinorRestrictions(
            minShiftStartHour: null,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A'],
            canFillSlot: true,
            requiresSupervisor: false,
            canSelfSignup: true
        );
        $this->assertTrue($categoryRestricted->hasRestrictions());

        // Minor with supervisor requirement
        $supervisorRequired = new MinorRestrictions(
            minShiftStartHour: null,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A', 'B', 'C'],
            canFillSlot: true,
            requiresSupervisor: true,
            canSelfSignup: true
        );
        $this->assertTrue($supervisorRequired->hasRestrictions());

        // Minor who cannot fill slot
        $cannotFillSlot = new MinorRestrictions(
            minShiftStartHour: null,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A', 'B', 'C'],
            canFillSlot: false,
            requiresSupervisor: false,
            canSelfSignup: true
        );
        $this->assertTrue($cannotFillSlot->hasRestrictions());

        // Minor who cannot self-signup
        $cannotSelfSignup = new MinorRestrictions(
            minShiftStartHour: null,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A', 'B', 'C'],
            canFillSlot: true,
            requiresSupervisor: false,
            canSelfSignup: false
        );
        $this->assertTrue($cannotSelfSignup->hasRestrictions());
    }
}
