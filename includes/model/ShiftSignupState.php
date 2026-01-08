<?php

namespace Engelsystem;

use Engelsystem\Models\Shifts\ShiftSignupStatus;

/**
 * BO to represent if there are free slots on a shift for a given angeltype
 * and if signup for a given user is possible (or not, because of collisions, etc.)
 */
class ShiftSignupState
{
    /** @var int */
    private $freeEntries;

    /**
     * ShiftSignupState constructor.
     *
     * @param ShiftSignupStatus $state
     * @param int               $free_entries
     * @param string[]          $minorErrors   Optional array of minor restriction error messages
     */
    public function __construct(
        private ShiftSignupStatus $state,
        $free_entries,
        private array $minorErrors = []
    ) {
        $this->freeEntries = $free_entries;
    }

    /**
     * Combine this state with another state from the same shift.
     *
     * @param ShiftSignupState $shiftSignupState The other state to combine
     */
    public function combineWith(ShiftSignupState $shiftSignupState)
    {
        $this->freeEntries += $shiftSignupState->getFreeEntries();

        // Merge minorErrors from both states to preserve all error messages
        $this->minorErrors = array_unique(array_merge(
            $this->minorErrors,
            $shiftSignupState->getMinorErrors()
        ));

        if ($this->valueForState($shiftSignupState->state) > $this->valueForState($this->state)) {
            $this->state = $shiftSignupState->state;
        }
    }

    /**
     * @param ShiftSignupStatus $state
     * @return int
     */
    private function valueForState(ShiftSignupStatus $state)
    {
        return match ($state) {
            ShiftSignupStatus::NOT_ARRIVED, ShiftSignupStatus::NOT_YET, ShiftSignupStatus::SHIFT_ENDED => 100,
            ShiftSignupStatus::SIGNED_UP => 90,
            ShiftSignupStatus::FREE      => 80,
            ShiftSignupStatus::MINOR_RESTRICTED => 75,
            ShiftSignupStatus::ANGELTYPE, ShiftSignupStatus::COLLIDES => 70,
            ShiftSignupStatus::OCCUPIED,  ShiftSignupStatus::ADMIN    => 60,
            default => 0,
        };
    }

    /**
     * Returns true, if signup is allowed
     *
     * @return bool
     */
    public function isSignupAllowed()
    {
        return match ($this->state) {
            ShiftSignupStatus::FREE, ShiftSignupStatus::ADMIN => true,
            default => false,
        };
    }

    /**
     * Return the shift signup state
     *
     * @return ShiftSignupStatus
     */
    public function getState(): ShiftSignupStatus
    {
        return $this->state;
    }

    /**
     * How many places are free in this shift for the angeltype?
     *
     * @return int
     */
    public function getFreeEntries()
    {
        return $this->freeEntries;
    }

    /**
     * Get the minor restriction error messages (if any).
     *
     * @return string[]
     */
    public function getMinorErrors(): array
    {
        return $this->minorErrors;
    }
}
