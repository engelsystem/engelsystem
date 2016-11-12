<?php

namespace Engelsystem;

/**
 * BO to represent if there are free slots on a shift for a given angeltype
 * and if signup for a given user is possible (or not, because of collisions, etc.)
 */
class ShiftSignupState {

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
   * User is already signed up
   */
  const SIGNED_UP = 'SIGNED_UP';

  private $state;

  private $freeEntries;

  public function __construct($state, $free_entries) {
    $this->state = $state;
    $this->freeEntries = $free_entries;
  }

  /**
   * Combine this state with another state from the same shift.
   *
   * @param ShiftSignupState $shiftSignupState
   *          The other state to combine
   */
  public function combineWith(ShiftSignupState $shiftSignupState) {
    $this->freeEntries += $shiftSignupState->getFreeEntries();
    
    switch ($this->state) {
      case ShiftSignupState::ANGELTYPE:
      case ShiftSignupState::OCCUPIED:
        $this->state = $shiftSignupState->getState();
    }
  }

  /**
   * Returns true, if signup is allowed
   */
  public function isSignupAllowed() {
    switch ($this->state) {
      case ShiftSignupState::FREE:
      case ShiftSignupState::ADMIN:
        return true;
    }
    return false;
  }

  /**
   * Return the shift signup state
   */
  public function getState() {
    return $this->state;
  }

  /**
   * How many places are free in this shift for the angeltype?
   */
  public function getFreeEntries() {
    return $this->freeEntries;
  }
}

?>