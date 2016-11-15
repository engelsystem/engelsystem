<?php

namespace Engelsystem;

class ShiftCalendarRenderer {

  /**
   * 15m * 60s/m = 900s
   */
  const SECONDS_PER_ROW = 900;

  /**
   * Height of a block in pixel.
   * Do not change - corresponds with theme/css
   */
  const BLOCK_HEIGHT = 30;

  /**
   * Distance between two shifts in pixels
   */
  const MARGIN = 5;

  /**
   * Seconds added to the start and end time
   */
  const TIME_MARGIN = 1800;

  private $lanes;

  private $shiftsFilter;

  private $firstBlockStartTime = null;

  private $lastBlockEndTime = null;

  private $blocksPerSlot = null;

  public function __construct($shifts, ShiftsFilter $shiftsFilter) {
    $this->shiftsFilter = $shiftsFilter;
    $this->firstBlockStartTime = $this->calcFirstBlockStartTime($shifts);
    $this->lastBlockEndTime = $this->calcLastBlockEndTime($shifts);
    $this->lanes = $this->assignShiftsToLanes($shifts);
  }

  /**
   * Assigns the shifts to different lanes per room if they collide
   *
   * @param Shift[] $shifts
   *          The shifts to assign
   *          
   * @return Returns an array that assigns a room_id to an array of ShiftCalendarLane containing the shifts
   */
  private function assignShiftsToLanes($shifts) {
    // array that assigns a room id to a list of lanes (per room)
    $lanes = [];
    
    foreach ($shifts as $shift) {
      $room_id = $shift['RID'];
      $header = Room_name_render([
          'RID' => $room_id,
          'Name' => $shift['room_name'] 
      ]);
      if (! isset($lanes[$room_id])) {
        // initialize room with one lane
        $lanes[$room_id] = [
            new ShiftCalendarLane($header, $this->getFirstBlockStartTime(), $this->getBlocksPerSlot()) 
        ];
      }
      // Try to add the shift to the existing lanes for this room
      $shift_added = false;
      foreach ($lanes[$room_id] as $lane) {
        $shift_added = $lane->addShift($shift);
        if ($shift_added == true) {
          break;
        }
      }
      // If all lanes for this room are busy, create a new lane and add shift to it
      if ($shift_added == false) {
        $newLane = new ShiftCalendarLane($header, $this->getFirstBlockStartTime(), $this->getBlocksPerSlot());
        if (! $newLane->addShift($shift)) {
          engelsystem_error("Unable to add shift to new lane.");
        }
        $lanes[$room_id][] = $newLane;
      }
    }
    
    return $lanes;
  }

  public function getFirstBlockStartTime() {
    return $this->firstBlockStartTime;
  }

  public function getLastBlockEndTime() {
    return $this->lastBlockEndTime;
  }

  public function getBlocksPerSlot() {
    if ($this->blocksPerSlot == null) {
      $this->blocksPerSlot = $this->calcBlocksPerSlot();
    }
    return $this->blocksPerSlot;
  }

  /**
   * Renders the whole calendar
   *
   * @return the generated html
   */
  public function render() {
    if (count($this->lanes) == 0) {
      return '';
    }
    return div('shift-calendar', [
        $this->renderTimeLane(),
        $this->renderShiftLanes() 
    ]) . $this->renderLegend();
  }

  /**
   * Renders the lanes containing the shifts
   */
  private function renderShiftLanes() {
    $html = "";
    foreach ($this->lanes as $room_lanes) {
      foreach ($room_lanes as $lane) {
        $html .= $this->renderLane($lane);
      }
    }
    return $html;
  }

  /**
   * Renders a single lane
   *
   * @param ShiftCalendarLane $lane
   *          The lane to render
   */
  private function renderLane(ShiftCalendarLane $lane) {
    global $user;
    
    $shift_renderer = new ShiftCalendarShiftRenderer();
    $html = "";
    $rendered_until = $this->getFirstBlockStartTime();
    foreach ($lane->getShifts() as $shift) {
      while ($rendered_until + ShiftCalendarRenderer::SECONDS_PER_ROW <= $shift['start']) {
        $html .= $this->renderTick($rendered_until);
        $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
      }
      
      list($shift_height, $shift_html) = $shift_renderer->render($shift, $user);
      $html .= $shift_html;
      $rendered_until += $shift_height * ShiftCalendarRenderer::SECONDS_PER_ROW;
    }
    while ($rendered_until < $this->getLastBlockEndTime()) {
      $html .= $this->renderTick($rendered_until);
      $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
    }
    
    return div('lane', [
        div('header', $lane->getHeader()),
        $html 
    ]);
  }

  /**
   * Renders a tick/block for given time
   *
   * @param int $time
   *          unix timestamp
   * @param boolean $label
   *          Should time labels be generated?
   * @return rendered tick html
   */
  private function renderTick($time, $label = false) {
    if ($time % (24 * 60 * 60) == 23 * 60 * 60) {
      if (! $label) {
        return div('tick day');
      }
      return div('tick day', [
          date('m-d<b\r />H:i', $time) 
      ]);
    } elseif ($time % (60 * 60) == 0) {
      if (! $label) {
        return div('tick hour');
      }
      return div('tick hour', [
          date('H:i', $time) 
      ]);
    }
    return div('tick');
  }

  /**
   * Renders the left time lane including hour/day ticks
   */
  private function renderTimeLane() {
    $time_slot = [
        div('header', [
            _("Time") 
        ]) 
    ];
    for ($block = 0; $block < $this->getBlocksPerSlot(); $block ++) {
      $thistime = $this->getFirstBlockStartTime() + ($block * ShiftCalendarRenderer::SECONDS_PER_ROW);
      $time_slot[] = $this->renderTick($thistime, true);
    }
    return div('lane time', $time_slot);
  }

  private function calcFirstBlockStartTime($shifts) {
    $start_time = $this->shiftsFilter->getEndTime();
    foreach ($shifts as $shift) {
      if ($shift['start'] < $start_time) {
        $start_time = $shift['start'];
      }
    }
    return ShiftCalendarRenderer::SECONDS_PER_ROW * floor(($start_time - ShiftCalendarRenderer::TIME_MARGIN) / ShiftCalendarRenderer::SECONDS_PER_ROW);
  }

  private function calcLastBlockEndTime($shifts) {
    $end_time = $this->shiftsFilter->getStartTime();
    foreach ($shifts as $shift) {
      if ($shift['end'] > $end_time) {
        $end_time = $shift['end'];
      }
    }
    return ShiftCalendarRenderer::SECONDS_PER_ROW * ceil(($end_time + ShiftCalendarRenderer::TIME_MARGIN) / ShiftCalendarRenderer::SECONDS_PER_ROW);
  }

  private function calcBlocksPerSlot() {
    return ceil(($this->getLastBlockEndTime() - $this->getFirstBlockStartTime()) / ShiftCalendarRenderer::SECONDS_PER_ROW);
  }

  /**
   * Renders a legend explaining the shift coloring
   */
  private function renderLegend() {
    return div('legend', [
        label(_('Your shift'), 'primary'),
        label(_('Help needed'), 'danger'),
        label(_('Other angeltype needed / collides with my shifts'), 'warning'),
        label(_('Shift is full'), 'success'),
        label(_('Shift running/ended'), 'default') 
    ]);
  }
}

?>