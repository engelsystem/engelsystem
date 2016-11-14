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
   * @param User $user
   *          The user who is viewing the shift calendar
   */
  public function render($shift, $user) {
    $info_text = "";
    if ($shift['title'] != '') {
      $info_text = glyph('info-sign') . $shift['title'] . '<br>';
    }
    list($shift_signup_state, $shifts_row) = $this->renderShiftNeededAngeltypes($shift, $user);
    $class = $this->classForSignupState($shift_signup_state);
    
    $blocks = ceil(($shift["end"] - $shift["start"]) / ShiftCalendarRenderer::SECONDS_PER_ROW);
    $blocks = max(1, $blocks);
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

  private function classForSignupState(ShiftSignupState $shiftSignupState) {
    switch ($shiftSignupState->getState()) {
      case ShiftSignupState::ADMIN:
      case ShiftSignupState::OCCUPIED:
        return 'success';
      
      case ShiftSignupState::SIGNED_UP:
        return 'primary';
      
      case ShiftSignupState::SHIFT_ENDED:
        return 'default';
      
      case ShiftSignupState::ANGELTYPE:
      case ShiftSignupState::COLLIDES:
        return 'warning';
      
      case ShiftSignupState::FREE:
        return 'danger';
    }
  }

  private function renderShiftNeededAngeltypes($shift, $user) {
    global $privileges;
    
    $html = "";
    $shift_signup_state = null;
    $angeltypes = NeededAngelTypes_by_shift($shift['SID']);
    foreach ($angeltypes as $angeltype) {
      list($angeltype_signup_state, $angeltype_html) = $this->renderShiftNeededAngeltype($shift, $angeltype, $user);
      if ($shift_signup_state == null) {
        $shift_signup_state = $angeltype_signup_state;
      } else {
        $shift_signup_state->combineWith($angeltype_signup_state);
      }
      $html .= $angeltype_html;
    }
    if (in_array('user_shifts_admin', $privileges)) {
      $html .= '<li class="list-group-item">' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _("Add more angels"), 'btn-xs') . '</li>';
    }
    if ($html != '') {
      return [
          $shift_signup_state,
          '<ul class="list-group">' . $html . '</ul>' 
      ];
    }
    return [
        $shift_signup_state,
        "" 
    ];
  }

  /**
   * Renders a list entry containing the needed angels for an angeltype
   *
   * @param Shift $shift
   *          The shift which is rendered
   * @param Angeltype $angeltype
   *          The angeltype, containing informations about needed angeltypes and already signed up angels
   * @param User $user
   *          The user who is viewing the shift calendar
   */
  private function renderShiftNeededAngeltype($shift, $angeltype, $user) {
    $entry_list = [];
    foreach ($angeltype['shift_entries'] as $entry) {
      $style = $entry['freeloaded'] ? " text-decoration: line-through;" : '';
      $entry_list[] = "<span style=\"$style\">" . User_Nick_render(User($entry['UID'])) . "</span>";
    }
    $shift_signup_state = Shift_signup_allowed($user, $shift, $angeltype);
    $inner_text = sprintf(ngettext("%d helper needed", "%d helpers needed", $shift_signup_state->getFreeEntries()), $shift_signup_state->getFreeEntries());
    switch ($shift_signup_state->getState()) {
      case ShiftSignupState::ADMIN:
      case ShiftSignupState::FREE:
        // When admin or free display a link + button for sign up
        $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'] . '">' . $inner_text . '</a> ' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _('Sign up'), 'btn-xs btn-primary');
        break;
      
      case ShiftSignupState::SHIFT_ENDED:
        // No link and add a text hint, when the shift ended
        $entry_list[] = $inner_text . ' (' . _('ended') . ')';
        break;
      
      case ShiftSignupState::ANGELTYPE:
        if ($angeltype['restricted'] == 1) {
          // User has to be confirmed on the angeltype first
          $entry_list[] = $inner_text . glyph('lock');
        } else {
          // Add link to join the angeltype first
          $entry_list[] = $inner_text . '<br />' . button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], sprintf(_('Become %s'), $angeltype['name']), 'btn-xs');
        }
        break;
      
      case ShiftSignupState::COLLIDES:
      case ShiftSignupState::SIGNED_UP:
        // Shift collides or user is already signed up: No signup allowed
        $entry_list[] = $inner_text;
        break;
      
      case ShiftSignupState::OCCUPIED:
        // Shift is full
        break;
    }
    
    $shifts_row = '<li class="list-group-item">';
    $shifts_row .= '<strong>' . AngelType_name_render($angeltype) . ':</strong> ';
    $shifts_row .= join(", ", $entry_list);
    $shifts_row .= '</li>';
    return [
        $shift_signup_state,
        $shifts_row 
    ];
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
}

?>