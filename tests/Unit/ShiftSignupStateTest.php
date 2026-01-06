<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects -- Required for loading legacy class

namespace Engelsystem\Test\Unit;

use Engelsystem\Models\Shifts\ShiftSignupStatus;

// Include legacy class that's not in PSR-4 autoload
require_once __DIR__ . '/../../includes/model/ShiftSignupState.php';

use Engelsystem\ShiftSignupState;

/**
 * @covers \Engelsystem\ShiftSignupState
 */
class ShiftSignupStateTest extends TestCase
{
    /**
     * @covers \Engelsystem\ShiftSignupState::__construct
     * @covers \Engelsystem\ShiftSignupState::getState
     * @covers \Engelsystem\ShiftSignupState::getFreeEntries
     */
    public function testConstruct(): void
    {
        $state = new ShiftSignupState(ShiftSignupStatus::FREE, 5);

        $this->assertEquals(ShiftSignupStatus::FREE, $state->getState());
        $this->assertEquals(5, $state->getFreeEntries());
        $this->assertEmpty($state->getMinorErrors());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::__construct
     * @covers \Engelsystem\ShiftSignupState::getMinorErrors
     */
    public function testConstructWithMinorErrors(): void
    {
        $errors = ['Error 1', 'Error 2'];
        $state = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 3, $errors);

        $this->assertEquals(ShiftSignupStatus::MINOR_RESTRICTED, $state->getState());
        $this->assertEquals(3, $state->getFreeEntries());
        $this->assertEquals($errors, $state->getMinorErrors());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::isSignupAllowed
     * @dataProvider signupAllowedProvider
     */
    public function testIsSignupAllowed(ShiftSignupStatus $status, bool $expected): void
    {
        $state = new ShiftSignupState($status, 1);

        $this->assertEquals($expected, $state->isSignupAllowed());
    }

    public static function signupAllowedProvider(): array
    {
        return [
            'FREE allows signup' => [ShiftSignupStatus::FREE, true],
            'ADMIN allows signup' => [ShiftSignupStatus::ADMIN, true],
            'OCCUPIED does not allow signup' => [ShiftSignupStatus::OCCUPIED, false],
            'COLLIDES does not allow signup' => [ShiftSignupStatus::COLLIDES, false],
            'ANGELTYPE does not allow signup' => [ShiftSignupStatus::ANGELTYPE, false],
            'SHIFT_ENDED does not allow signup' => [ShiftSignupStatus::SHIFT_ENDED, false],
            'NOT_YET does not allow signup' => [ShiftSignupStatus::NOT_YET, false],
            'SIGNED_UP does not allow signup' => [ShiftSignupStatus::SIGNED_UP, false],
            'NOT_ARRIVED does not allow signup' => [ShiftSignupStatus::NOT_ARRIVED, false],
            'MINOR_RESTRICTED does not allow signup' => [ShiftSignupStatus::MINOR_RESTRICTED, false],
        ];
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     */
    public function testCombineWith(): void
    {
        // Test combining FREE entries
        $state1 = new ShiftSignupState(ShiftSignupStatus::FREE, 2);
        $state2 = new ShiftSignupState(ShiftSignupStatus::OCCUPIED, 3);

        $state1->combineWith($state2);

        $this->assertEquals(5, $state1->getFreeEntries());
        // FREE (80) should win over OCCUPIED (60)
        $this->assertEquals(ShiftSignupStatus::FREE, $state1->getState());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     */
    public function testCombineWithMinorRestricted(): void
    {
        // Test combining when one state is MINOR_RESTRICTED
        $state1 = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 2);
        $state2 = new ShiftSignupState(ShiftSignupStatus::FREE, 3);

        $state1->combineWith($state2);

        // FREE (80) > MINOR_RESTRICTED (75) - state should become FREE
        $this->assertEquals(ShiftSignupStatus::FREE, $state1->getState());
        $this->assertEquals(5, $state1->getFreeEntries());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     */
    public function testCombineWithMinorRestrictedWins(): void
    {
        // Test that MINOR_RESTRICTED wins over lower priority states
        $state1 = new ShiftSignupState(ShiftSignupStatus::OCCUPIED, 2);
        $state2 = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 3);

        $state1->combineWith($state2);

        // MINOR_RESTRICTED (75) > OCCUPIED (60) - state should become MINOR_RESTRICTED
        $this->assertEquals(ShiftSignupStatus::MINOR_RESTRICTED, $state1->getState());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     */
    public function testCombineWithSignedUpHighestPriority(): void
    {
        // Test that SIGNED_UP remains highest priority
        $state1 = new ShiftSignupState(ShiftSignupStatus::SIGNED_UP, 0);
        $state2 = new ShiftSignupState(ShiftSignupStatus::FREE, 5);

        $state1->combineWith($state2);

        // SIGNED_UP (90) > FREE (80) - state should remain SIGNED_UP
        $this->assertEquals(ShiftSignupStatus::SIGNED_UP, $state1->getState());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     */
    public function testCombineWithNotArrivedHighestPriority(): void
    {
        // Test that NOT_ARRIVED is highest priority
        $state1 = new ShiftSignupState(ShiftSignupStatus::FREE, 5);
        $state2 = new ShiftSignupState(ShiftSignupStatus::NOT_ARRIVED, 0);

        $state1->combineWith($state2);

        // NOT_ARRIVED (100) > FREE (80) - state should become NOT_ARRIVED
        $this->assertEquals(ShiftSignupStatus::NOT_ARRIVED, $state1->getState());
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     * @covers \Engelsystem\ShiftSignupState::getMinorErrors
     */
    public function testCombineWithMergesMinorErrors(): void
    {
        // Test that minorErrors are merged when combining states
        $errors1 = ['Shift exceeds daily hour limit', 'Shift starts too early'];
        $errors2 = ['Shift ends too late', 'Shift exceeds daily hour limit']; // Duplicate should be removed

        $state1 = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 2, $errors1);
        $state2 = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 3, $errors2);

        $state1->combineWith($state2);

        $mergedErrors = $state1->getMinorErrors();

        // Should have 3 unique errors (duplicate 'Shift exceeds daily hour limit' removed)
        $this->assertCount(3, $mergedErrors);
        $this->assertContains('Shift exceeds daily hour limit', $mergedErrors);
        $this->assertContains('Shift starts too early', $mergedErrors);
        $this->assertContains('Shift ends too late', $mergedErrors);
    }

    /**
     * @covers \Engelsystem\ShiftSignupState::combineWith
     * @covers \Engelsystem\ShiftSignupState::getMinorErrors
     */
    public function testCombineWithPreservesMinorErrorsWhenStateChanges(): void
    {
        // Test that minorErrors are preserved even when the state changes to FREE
        $errors = ['Shift exceeds daily hour limit'];

        $state1 = new ShiftSignupState(ShiftSignupStatus::MINOR_RESTRICTED, 2, $errors);
        $state2 = new ShiftSignupState(ShiftSignupStatus::FREE, 3);

        $state1->combineWith($state2);

        // State should change to FREE (higher priority)
        $this->assertEquals(ShiftSignupStatus::FREE, $state1->getState());
        // But minorErrors should still be preserved
        $this->assertContains('Shift exceeds daily hour limit', $state1->getMinorErrors());
    }
}
