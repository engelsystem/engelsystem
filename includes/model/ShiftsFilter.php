<?php

namespace Engelsystem;

/**
 * BO Class that stores all parameters used to filter shifts for users.
 *
 * @author msquare
 */
class ShiftsFilter {

  /**
   * Shift is completely full.
   */
  const FILLED_FILLED = 1;

  /**
   * Shift has some free slots.
   */
  const FILLED_FREE = 0;

  /**
   * Has the user "user shifts admin" privilege?
   *
   * @var boolean
   */
  private $userShiftsAdmin;

  private $filled = [];

  private $rooms = [];

  private $types = [];

  private $startTime = null;

  private $endTime = null;

  public function __construct($user_shifts_admin, $rooms, $types) {
    $this->user_shifts_admin = $user_shifts_admin;
    $this->rooms = $rooms;
    $this->types = $types;
    
    $this->filled = [
        ShiftsFilter::FILLED_FREE 
    ];
    
    if ($user_shifts_admin) {
      $this->filled[] = ShiftsFilter::FILLED_FILLED;
    }
  }

  public function getStartTime() {
    return $this->startTime;
  }

  public function setStartTime($startTime) {
    $this->startTime = $startTime;
  }

  public function getEndTime() {
    return $this->endTime;
  }

  public function setEndTime($endTime) {
    $this->endTime = $endTime;
  }

  public function getTypes() {
    if (count($this->types) == 0) {
      return [
          0 
      ];
    }
    return $this->types;
  }

  public function setTypes($types) {
    $this->types = $types;
  }

  public function getRooms() {
    if (count($this->rooms) == 0) {
      return [
          0 
      ];
    }
    return $this->rooms;
  }

  public function setRooms($rooms) {
    $this->rooms = $rooms;
  }

  public function isUserShiftsAdmin() {
    return $this->userShiftsAdmin;
  }

  public function setUserShiftsAdmin($userShiftsAdmin) {
    $this->userShiftsAdmin = $userShiftsAdmin;
  }

  public function getFilled() {
    return $this->filled;
  }

  public function setFilled($filled) {
    $this->filled = $filled;
  }
}

?>