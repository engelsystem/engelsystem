<?php

namespace Engelsystem;

/**
 * Renders a single shift for the shift calendar
 */
class ShiftCalendarShiftRenderer {

  /**
   * Renders a shift
   *
   * @param Shift $shift
   *          The shift to render
   */
  public function render($shift) {
    global $privileges;
    
    $collides = $this->collides();
    $is_free = false;
    $shifts_row = '';
    $info_text = "";
    if ($shift['title'] != '') {
      $info_text = glyph('info-sign') . $shift['title'] . '<br>';
    }
    
    $angeltypes = NeededAngelTypes_by_shift($shift['SID']);
    foreach ($angeltypes as $angeltype) {
      $shifts_row .= $this->renderShiftNeededAngeltype($shift, $angeltype, $collides);
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
    return [
        $blocks,
        '<td class="shift" rowspan="' . $blocks . '">' . div('shift panel panel-' . $class . '" style="height: ' . ($blocks * ShiftCalendarRenderer::BLOCK_HEIGHT - ShiftCalendarRenderer::MARGIN) . 'px"', [
            $this->renderShiftHead($shift),
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

  /**
   * Renders a list entry containing the needed angels for an angeltype
   *
   * @param Shift $shift
   *          The shift which is rendered
   * @param Angeltype $angeltype
   *          The angeltype, containing informations about needed angeltypes and already signed up angels
   * @param boolean $collides
   *          true if the shift collides with the users shifts
   */
  private function renderShiftNeededAngeltype($shift, $angeltype, $collides) {
    global $privileges;
    
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
    }
    
    $shifts_row = '<li class="list-group-item">';
    $shifts_row .= '<strong>' . AngelType_name_render($angeltype) . ':</strong> ';
    $shifts_row .= join(", ", $entry_list);
    $shifts_row .= '</li>';
    return $shifts_row;
  }

  /**
   * Renders the shift header
   *
   * @param Shift $shift
   *          The shift
   */
  private function renderShiftHead($shift) {
    global $privileges;
    
    $header_buttons = "";
    if (in_array('admin_shifts', $privileges)) {
      $header_buttons = '<div class="pull-right">' . table_buttons([
          button(page_link_to('user_shifts') . '&edit_shift=' . $shift['SID'], glyph('edit'), 'btn-xs'),
          button(page_link_to('user_shifts') . '&delete_shift=' . $shift['SID'], glyph('trash'), 'btn-xs') 
      ]) . '</div>';
    }
    $shift_heading = date('H:i', $shift['start']) . ' &dash; ' . date('H:i', $shift['end']) . ' &mdash; ' . ShiftType($shift['shifttype_id'])['name'];
    return div('panel-heading', [
        '<a href="' . shift_link($shift) . '">' . $shift_heading . '</a>',
        $header_buttons 
    ]);
  }

  /**
   * Does the shift collide with the user's shifts
   */
  private function collides() {
    // TODO
    return false;
  }
}

?>