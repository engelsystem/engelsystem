<?php

namespace Engelsystem;

class ShiftCalendarRenderer {

  /**
   * 15m * 60s/m = 900s
   */
  const MINUTES_PER_ROW = 900;

  private $shifts;

  private $shiftsFilter;

  public function __construct($shifts, ShiftsFilter $shiftsFilter) {
    $this->shifts = $shifts;
    $this->shiftsFilter = $shiftsFilter;
  }

  public function render() {
    $rooms = $this->rooms();
    $slotSizes = $this->calcSlotSizes($rooms);
    
    return '';
  }

  /**
   * Calculates the slots for each room that appears in the shifts
   */
  private function rooms() {
    $rooms = [];
    foreach ($this->shifts as $shift) {
      if (! isset($rooms[$shift['RID']])) {
        $rooms[$shift['RID']] = $shift['room_name'];
      }
    }
    return $rooms;
  }

  private function calcSlotSizes($rooms) {
    $first_block_start_time = ShiftCalendarRenderer::MINUTES_PER_ROW * floor($this->shiftsFilter->getStartTime() / ShiftCalendarRenderer::MINUTES_PER_ROW);
    $blocks_per_slot = ceil(($this->shiftsFilter->getEndTime() - $first_block_start_time) / ShiftCalendarRenderer::MINUTES_PER_ROW);
    $parallel_blocks = [];
    
    // initialize $block array
    foreach (array_keys($rooms) as $room_id) {
      $parallel_blocks[$room_id] = array_fill(0, $blocks_per_slot, 0);
    }
    
    // calculate number of parallel shifts in each timeslot for each room
    foreach ($this->shifts as $shift) {
      $room_id = $shift["RID"];
      $shift_blocks = ($shift["end"] - $shift["start"]) / ShiftCalendarRenderer::MINUTES_PER_ROW;
      $firstblock = floor(($shift["start"] - $first_block_start_time) / ShiftCalendarRenderer::MINUTES_PER_ROW);
      for ($block = $firstblock; $block < $shift_blocks + $firstblock && $block < $blocks_per_slot; $block ++) {
        $parallel_blocks[$room_id][$block] ++;
      }
    }
    
    return array_map('max', $parallel_blocks);
  }
}

?>