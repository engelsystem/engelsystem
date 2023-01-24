<?php

namespace Engelsystem;

use Engelsystem\Models\Shifts\ShiftSignupStatus;

/**
 * BO to represent if there are free slots on a shift for a given angeltype
 * and if signup for a given user is possible (or not, because of collisions, etc.)
 */
class ShiftSignupState
{
    /**
     * ShiftSignupState constructor.
     */
    public function __construct(private ShiftSignupStatus $state, private int $freeEntries)
    {
    }

    /**
     * Combine this state with another state from the same shift.
     */
    public function combineWith(ShiftSignupState $shiftSignupState): void
    {
        $this->freeEntries += $shiftSignupState->getFreeEntries();

        if ($this->valueForState($shiftSignupState->state) > $this->valueForState($this->state)) {
            $this->state = $shiftSignupState->state;
        }
    }

    private function valueForState(ShiftSignupStatus $state): int
    {
        return match ($state) {
            ShiftSignupStatus::NOT_ARRIVED, ShiftSignupStatus::NOT_YET, ShiftSignupStatus::SHIFT_ENDED => 100,
            ShiftSignupStatus::SIGNED_UP => 90,
            ShiftSignupStatus::FREE      => 80,
            ShiftSignupStatus::ANGELTYPE, ShiftSignupStatus::COLLIDES => 70,
            ShiftSignupStatus::OCCUPIED,  ShiftSignupStatus::ADMIN    => 60,
            default => 0,
        };
    }

    /**
     * Returns true, if signup is allowed
     */
    public function isSignupAllowed(): bool
    {
        return match ($this->state) {
            ShiftSignupStatus::FREE, ShiftSignupStatus::ADMIN => true,
            default => false,
        };
    }

    /**
     * Return the shift signup state
     */
    public function getState(): ShiftSignupStatus
    {
        return $this->state;
    }

    /**
     * How many places are free in this shift for the angeltype?
     */
    public function getFreeEntries(): int
    {
        return $this->freeEntries;
    }
}
