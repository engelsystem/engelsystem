<?php

namespace Engelsystem;

class ShiftCalendarRenderer {

  /**
   * 15m * 60s/m = 900s
   */
  const MINUTES_PER_ROW = 900;

  const EMPTY_CELL = '<td class="empty"></td>';

  private $shifts;

  private $shiftsFilter;

  public function __construct($shifts, ShiftsFilter $shiftsFilter) {
    $this->shifts = $shifts;
    $this->shiftsFilter = $shiftsFilter;
  }

  public function render() {
    $rooms = $this->rooms();
    
    $first_block_start_time = $this->calcFirstBlockStartTime();
    $blocks_per_slot = $this->calcBlocksPerSlot($first_block_start_time);
    
    $slotSizes = $this->calcSlotSizes($rooms, $first_block_start_time, $blocks_per_slot);
    
    return $this->renderTable($rooms, $slotSizes, $first_block_start_time, $blocks_per_slot);
  }

  private function renderTableHead($rooms, $slotSizes) {
    $shifts_table = '<thead><tr><th>' . _("Time") . '</th>';
    foreach ($rooms as $room_id => $room_name) {
      $colspan = $slotSizes[$room_id];
      $shifts_table .= "<th" . (($colspan > 1) ? ' colspan="' . $colspan . '"' : '') . ">" . Room_name_render([
          'RID' => $room_id,
          'Name' => $room_name 
      ]) . "</th>\n";
    }
    $shifts_table .= "</tr></thead>";
    return $shifts_table;
  }

  private function initTableBody($slotSizes, $first_block_start_time, $blocks_per_slot) {
    // Slot sizes plus 1 for the time
    $columns_needed = array_sum($slotSizes) + 1;
    $table_line = array_fill(0, $columns_needed, ShiftCalendarRenderer::EMPTY_CELL);
    $table = array_fill(0, $blocks_per_slot, $table_line);
    
    for ($block = 0; $block < $blocks_per_slot; $block ++) {
      $thistime = $first_block_start_time + ($block * ShiftCalendarRenderer::MINUTES_PER_ROW);
      if ($thistime % (24 * 60 * 60) == 23 * 60 * 60 && $this->shiftsFilter->getEndTime() - $this->shiftsFilter->getStartTime() > 24 * 60 * 60) {
        $table[$block][0] = '<th class="row-day">' . date('Y-m-d<b\r />H:i', $thistime) . '</th>';
      } elseif ($thistime % (60 * 60) == 0) {
        $table[$block][0] = '<th class="row-hour">' . date('H:i', $thistime) . '</th>';
      } else {
        $table[$block][0] = '<th class="empty"></th>';
      }
    }
    
    return $table;
  }

  private function calcRoomSlots($rooms, $slotSizes) {
    $result = [];
    $slot = 1; // 1 for the time
    foreach (array_keys($rooms) as $room_id) {
      $result[$room_id] = $slot;
      $slot += $slotSizes[$room_id];
    }
    
    return $result;
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
    
    $blocks = ceil(($shift["end"] - $shift["start"]) / ShiftCalendarRenderer::MINUTES_PER_ROW);
    if ($blocks < 1) {
      $blocks = 1;
    }
    $shift_length = ($shift["end"] - $shift["start"]) / (60 * 60);
    $shift_heading = date('H:i', $shift['start']) . ' &dash; ' . date('H:i', $shift['end']) . ' &mdash; ' . ShiftType($shift['shifttype_id'])['name'];
    return [
        $blocks,
        '<td class="shift" rowspan="' . $blocks . '">' . div('panel panel-' . $class . '" style="min-height: ' . ($shift_length * 100) . 'px"', [
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

  private function renderTableBody($rooms, $slotSizes, $first_block_start_time, $blocks_per_slot) {
    $table = $this->initTableBody($slotSizes, $first_block_start_time, $blocks_per_slot);
    
    $room_slots = $this->calcRoomSlots($rooms, $slotSizes);
    
    foreach ($this->shifts as $shift) {
      list($blocks, $shift_content) = $this->renderShift($shift);
      $start_block = floor(($shift['start'] - $first_block_start_time) / ShiftCalendarRenderer::MINUTES_PER_ROW);
      $slot = $room_slots[$shift['RID']];
      while ($table[$start_block][$slot] != ShiftCalendarRenderer::EMPTY_CELL) {
        $slot ++;
      }
      $table[$start_block][$slot] = $shift_content;
      for ($block = 1; $block < $blocks; $block ++) {
        $table[$start_block + $block][$slot] = '';
      }
    }
    
    $result = '<tbody>';
    foreach ($table as $table_line) {
      $result .= '<tr>' . join('', $table_line) . '</tr>';
    }
    $result .= '</tbody>';
    return $result;
  }

  private function renderTable($rooms, $slotSizes, $first_block_start_time, $blocks_per_slot) {
    return div('shifts-table', [
        '<table id="shifts" class="table scrollable">',
        $this->renderTableHead($rooms, $slotSizes),
        $this->renderTableBody($rooms, $slotSizes, $first_block_start_time, $blocks_per_slot),
        '</table>' 
    ]);
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

  private function calcFirstBlockStartTime() {
    $start_time = $this->shiftsFilter->getEndTime();
    foreach ($this->shifts as $shift) {
      if ($shift['start'] < $start_time) {
        $start_time = $shift['start'];
      }
    }
    return ShiftCalendarRenderer::MINUTES_PER_ROW * floor(($start_time - 60 * 60) / ShiftCalendarRenderer::MINUTES_PER_ROW);
  }

  private function calcBlocksPerSlot($first_block_start_time) {
    return ceil(($this->shiftsFilter->getEndTime() - $first_block_start_time) / ShiftCalendarRenderer::MINUTES_PER_ROW);
  }

  private function calcSlotSizes($rooms, $first_block_start_time, $blocks_per_slot) {
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