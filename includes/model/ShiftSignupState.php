<?php

namespace Engelsystem;

/**
 * BO to represent if there are free slots on a shift for a given angeltype
 * and if signup for a given user is possible (or not, because of collisions, etc.)
 */
class ShiftSignupState
{
    /**
     * Shift has free places
     */
    public const FREE = 'FREE';

    /**
     * Shift collides with users shifts
     */
    public const COLLIDES = 'COLLIDES';

    /**
     * User cannot join because of a restricted angeltype or user is not in the angeltype
     */
    public const ANGELTYPE = 'ANGELTYPE';

    /**
     * Shift is full
     */
    public const OCCUPIED = 'OCCUPIED';

    /**
     * User is admin and can do what he wants.
     */
    public const ADMIN = 'ADMIN';

    /**
     * Shift has already ended, no signup
     */
    public const SHIFT_ENDED = 'SHIFT_ENDED';

    /**
     * Shift is not available yet
     */
    public const NOT_YET = 'NOT_YET';

    /**
     * User is already signed up
     */
    public const SIGNED_UP = 'SIGNED_UP';

    /**
     * User has to be arrived
     */
    public const NOT_ARRIVED = 'NOT_ARRIVED';

    /** @var int */
    private $freeEntries;

    /**
     * ShiftSignupState constructor.
     *
     * @param string $state
     * @param int    $free_entries
     */
    public function __construct(private $state, $free_entries)
    {
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

        if ($this->valueForState($shiftSignupState->state) > $this->valueForState($this->state)) {
            $this->state = $shiftSignupState->state;
        }
    }

    /**
     * @param string $state
     * @return int
     */
    private function valueForState($state)
    {
        return match ($state) {
            ShiftSignupState::NOT_ARRIVED, ShiftSignupState::NOT_YET, ShiftSignupState::SHIFT_ENDED => 100,
            ShiftSignupState::SIGNED_UP => 90,
            ShiftSignupState::FREE      => 80,
            ShiftSignupState::ANGELTYPE, ShiftSignupState::COLLIDES => 70,
            ShiftSignupState::OCCUPIED, ShiftSignupState::ADMIN     => 60,
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
            ShiftSignupState::FREE, ShiftSignupState::ADMIN => true,
            default => false,
        };
    }

    /**
     * Return the shift signup state
     *
     * @return string
     */
    public function getState()
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
}
