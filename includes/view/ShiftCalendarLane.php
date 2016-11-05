<?php

namespace Engelsystem;

/**
 * Represents a single lane in a shifts calendar.
 */
class ShiftCalendarLane {

  private $firstBlockStartTime;

  private $blockCount;

  private $header;

  private $shifts = [];

  public function __construct($header, $firstBlockStartTime, $blockCount) {
    $this->header = $header;
    $this->firstBlockStartTime = $firstBlockStartTime;
    $this->blockCount = $blockCount;
  }

  /**
   * Adds a shift to the lane, but only if it fits.
   * Returns true on success.
   *
   * @param Shift $shift
   *          The shift to add
   * @return boolean true on success
   */
  public function addShift($shift) {
    if ($this->shiftFits($shift)) {
      $this->shifts[] = $shift;
      return true;
    }
    return false;
  }

  /**
   * Returns true if given shift fits into this lane.
   *
   * @param Shift $shift
   *          The shift to fit into this lane
   */
  public function shiftFits($newShift) {
    foreach ($this->shifts as $laneShift) {
      if (! ($newShift['start'] >= $laneShift['end'] || $newShift['end'] <= $laneShift['start'])) {
        return false;
      }
    }
    return true;
  }

  public function getHeader() {
    return $this->header;
  }

  public function getShifts() {
    return $this->shifts;
  }
}
?>