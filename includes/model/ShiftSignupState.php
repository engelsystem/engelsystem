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
    const FREE = 'FREE';

    /**
     * Shift collides with users shifts
     */
    const COLLIDES = 'COLLIDES';

    /**
     * User cannot join because of a restricted angeltype or user is not in the angeltype
     */
    const ANGELTYPE = 'ANGELTYPE';

    /**
     * Shift is full
     */
    const OCCUPIED = 'OCCUPIED';

    /**
     * User is admin and can do what he wants.
     */
    const ADMIN = 'ADMIN';

    /**
     * Shift has already ended, no signup
     */
    const SHIFT_ENDED = 'SHIFT_ENDED';

    /**
     * Shift is not available yet
     */
    const NOT_YET = 'NOT_YET';

    /**
     * User is already signed up
     */
    const SIGNED_UP = 'SIGNED_UP';

    /**
     * User has to be arrived
     */
    const NOT_ARRIVED = 'NOT_ARRIVED';

    /** @var string */
    private $state;

    /** @var int */
    private $freeEntries;

    /**
     * ShiftSignupState constructor.
     *
     * @param string $state
     * @param int    $free_entries
     */
    public function __construct($state, $free_entries)
    {
        $this->state = $state;
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
        switch ($state) {
            case ShiftSignupState::NOT_ARRIVED:
            case ShiftSignupState::NOT_YET:
            case ShiftSignupState::SHIFT_ENDED:
                return 100;

            case ShiftSignupState::SIGNED_UP:
                return 90;

            case ShiftSignupState::FREE:
                return 80;

            case ShiftSignupState::ANGELTYPE:
            case ShiftSignupState::COLLIDES:
                return 70;

            case ShiftSignupState::OCCUPIED:
            case ShiftSignupState::ADMIN:
                return 60;

            default:
                return 0;
        }
    }

    /**
     * Returns true, if signup is allowed
     *
     * @return bool
     */
    public function isSignupAllowed()
    {
        switch ($this->state) {
            case ShiftSignupState::FREE:
            case ShiftSignupState::ADMIN:
                return true;
        }

        return false;
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
