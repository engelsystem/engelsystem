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

  private $lanes;

  private $shiftsFilter;

  private $firstBlockStartTime = null;

  private $blocksPerSlot = null;

  public function __construct($shifts, ShiftsFilter $shiftsFilter) {
    $this->shiftsFilter = $shiftsFilter;
    $this->firstBlockStartTime = $this->calcFirstBlockStartTime($shifts);
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
      if (! isset($lanes[$room_id])) {
        // initialize room with one lane
        $header = Room_name_render([
            'RID' => $room_id,
            'Name' => $shift['room_name'] 
        ]);
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
        $newLane = new ShiftCalendarLane("", $this->getFirstBlockStartTime(), $this->getBlocksPerSlot());
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
    return div('shift-calendar', [
        $this->renderTimeLane(),
        $this->renderShiftLanes() 
    ]);
  }

  /**
   * Renders the lanes containing the shifts
   */
  private function renderShiftLanes() {
    $html = "";
    foreach ($this->lanes as $room_id => $room_lanes) {
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
    $html = "";
    $rendered_until = $this->getFirstBlockStartTime();
    foreach ($lane->getShifts() as $shift) {
      while ($rendered_until + ShiftCalendarRenderer::SECONDS_PER_ROW <= $shift['start']) {
        $html .= $this->renderTick($rendered_until);
        $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
      }
      
      list($shift_height, $shift_html) = $this->renderShift($shift);
      $html .= $shift_html;
      $rendered_until += $shift_height * ShiftCalendarRenderer::SECONDS_PER_ROW;
    }
    while ($rendered_until <= $this->shiftsFilter->getEndTime()) {
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
          date('Y-m-d<b\r />H:i', $time) 
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

  private function collides() {
    // TODO
    return false;
  }

  private function renderShift($shift) {
    global $privileges;
    
    $collides = $this->collides();
    $is_free = false;
    $shifts_row = '';
    $header_buttons = "";
    if (in_array('admin_shifts', $privileges)) {
      $header_buttons = '<div class="pull-right">' . table_buttons([
          button(page_link_to('user_shifts') . '&edit_shift=' . $shift['SID'], glyph('edit'), 'btn-xs'),
          button(page_link_to('user_shifts') . '&delete_shift=' . $shift['SID'], glyph('trash'), 'btn-xs') 
      ]) . '</div>';
    }
    $info_text = "";
    if ($shift['title'] != '') {
      $info_text = glyph('info-sign') . $shift['title'] . '<br>';
    }
    
    $angeltypes = NeededAngelTypes_by_shift($shift['SID']);
    foreach ($angeltypes as $angeltype) {
      $entry_list = [];
      $freeloader = 0;
      foreach ($angeltype['shift_entries'] as $entry) {
        $style = '';
        if ($entry['freeloaded']) {
          $freeloader ++;
          $style = " text-decoration: line-through;";
        }
        $entry_list[] = "<span style=\"$style\">" . User_Nick_render(User($entry['UID'])) . "</span>";
      }
      if ($angeltype['count'] - count($angeltype['shift_entries']) - $freeloader > 0) {
        $inner_text = sprintf(ngettext("%d helper needed", "%d helpers needed", $angeltype['count'] - count($angeltype['shift_entries'])), $angeltype['count'] - count($angeltype['shift_entries']));
        // is the shift still running or alternatively is the user shift admin?
        $user_may_join_shift = true;
        
        // you cannot join if user alread joined a parallel or this shift
        $user_may_join_shift &= ! $collides;
        
        // you cannot join if user is not of this angel type
        $user_may_join_shift &= isset($angeltype['user_id']);
        
        // you cannot join if you are not confirmed
        if ($angeltype['restricted'] == 1 && isset($angeltype['user_id'])) {
          $user_may_join_shift &= isset($angeltype['confirm_user_id']);
        }
        
        // you can only join if the shift is in future or running
        $user_may_join_shift &= time() < $shift['start'];
        
        // User shift admins may join anybody in every shift
        $user_may_join_shift |= in_array('user_shifts_admin', $privileges);
        if ($user_may_join_shift) {
          $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'] . '">' . $inner_text . '</a> ' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _('Sign up'), 'btn-xs btn-primary');
        } else {
          if (time() > $shift['start']) {
            $entry_list[] = $inner_text . ' (' . _('ended') . ')';
          } elseif ($angeltype['restricted'] == 1 && isset($angeltype['user_id']) && ! isset($angeltype['confirm_user_id'])) {
            $entry_list[] = $inner_text . glyph('lock');
          } elseif ($angeltype['restricted'] == 1) {
            $entry_list[] = $inner_text;
          } elseif ($collides) {
            $entry_list[] = $inner_text;
          } else {
            $entry_list[] = $inner_text . '<br />' . button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], sprintf(_('Become %s'), $angeltype['name']), 'btn-xs');
          }
        }
        
        unset($inner_text);
        $is_free = true;
      }
      
      $shifts_row .= '<li class="list-group-item">';
      $shifts_row .= '<strong>' . AngelType_name_render($angeltype) . ':</strong> ';
      $shifts_row .= join(", ", $entry_list);
      $shifts_row .= '</li>';
    }
    if (in_array('user_shifts_admin', $privileges)) {
      $shifts_row .= '<li class="list-group-item">' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _("Add more angels"), 'btn-xs') . '</li>';
    }
    if ($shifts_row != '') {
      $shifts_row = '<ul class="list-group">' . $shifts_row . '</ul>';
    }
    if (isset($shift['own']) && $shift['own'] && ! in_array('user_shifts_admin', $privileges)) {
      $class = 'primary';
    } elseif ($collides && ! in_array('user_shifts_admin', $privileges)) {
      $class = 'default';
    } elseif ($is_free) {
      $class = 'danger';
    } else {
      $class = 'success';
    }
    
    $blocks = ceil(($shift["end"] - $shift["start"]) / ShiftCalendarRenderer::SECONDS_PER_ROW);
    if ($blocks < 1) {
      $blocks = 1;
    }
    $shift_heading = date('H:i', $shift['start']) . ' &dash; ' . date('H:i', $shift['end']) . ' &mdash; ' . ShiftType($shift['shifttype_id'])['name'];
    return [
        $blocks,
        '<td class="shift" rowspan="' . $blocks . '">' . div('shift panel panel-' . $class . '" style="height: ' . ($blocks * ShiftCalendarRenderer::BLOCK_HEIGHT - ShiftCalendarRenderer::MARGIN) . 'px"', [
            div('panel-heading', [
                '<a href="' . shift_link($shift) . '">' . $shift_heading . '</a>',
                $header_buttons 
            ]),
            div('panel-body', [
                $info_text,
                Room_name_render([
                    'RID' => $shift['RID'],
                    'Name' => $shift['room_name'] 
                ]) 
            ]),
            $shifts_row,
            div('shift-spacer') 
        ]) . '</td>' 
    ];
  }

  private function calcFirstBlockStartTime($shifts) {
    $start_time = $this->shiftsFilter->getEndTime();
    foreach ($shifts as $shift) {
      if ($shift['start'] < $start_time) {
        $start_time = $shift['start'];
      }
    }
    return ShiftCalendarRenderer::SECONDS_PER_ROW * floor(($start_time - 60 * 60) / ShiftCalendarRenderer::SECONDS_PER_ROW);
  }

  private function calcBlocksPerSlot() {
    return ceil(($this->shiftsFilter->getEndTime() - $this->getFirstBlockStartTime()) / ShiftCalendarRenderer::SECONDS_PER_ROW);
  }
}

?>