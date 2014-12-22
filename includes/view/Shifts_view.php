<?php

function Shift_view($shift, $shifttype, $room, $shift_admin, $angeltypes_source, $user_shift_admin) {
  $parsedown = new Parsedown();
  
  $angeltypes = [];
  foreach ($angeltypes_source as $angeltype)
    $angeltypes[$angeltype['id']] = $angeltype;
  
  $needed_angels = '';
  foreach ($shift['NeedAngels'] as $needed_angeltype) {
    $class = 'progress-bar-warning';
    if ($needed_angeltype['taken'] == 0)
      $class = 'progress-bar-danger';
    if ($needed_angeltype['taken'] >= $needed_angeltype['count'])
      $class = 'progress-bar-success';
    $needed_angels .= '<div class="list-group-item">';
    $needed_angels .= '<div class="pull-right">' . button(page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $needed_angeltype['TID'], _('Sign up')) . '</div>';
    $needed_angels .= '<h3>' . AngelType_name_render($angeltypes[$needed_angeltype['TID']]) . '</h3>';
    $needed_angels .= progress_bar(0, $needed_angeltype['count'], $needed_angeltype['taken'], $class, $needed_angeltype['taken'] . ' / ' . $needed_angeltype['count']);
    
    $angels = [];
    foreach ($shift['ShiftEntry'] as $shift_entry) {
      if ($shift_entry['TID'] == $needed_angeltype['TID']) {
        $entry = User_Nick_render(User($shift_entry['UID']));
        if ($shift_entry['freeloaded'])
          $entry = '<strike>' . $entry . '</strike>';
        if ($user_shift_admin) {
          $entry .= ' <div class="btn-group">';
          $entry .= button_glyph(page_link_to('user_myshifts') . '&edit=' . $shift['SID'] . '&id=' . $shift_entry['UID'], 'pencil', 'btn-xs');
          $entry .= button_glyph(page_link_to('user_shifts') . '&entry_id=' . $shift_entry['id'], 'trash', 'btn-xs');
          $entry .= '</div>';
        }
        $angels[] = $entry;
      }
    }
    
    $needed_angels .= join(', ', $angels);
    
    $needed_angels .= '</div>';
  }
  
  return page_with_title($shift['name'] . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>', [
      msg(),
      $shift_admin ? buttons([
          button(shift_edit_link($shift), glyph('pencil') . _('edit')),
          button(shift_delete_link($shift), glyph('trash') . _('delete')) 
      ]) : '',
      div('row', [
          div('col-sm-3 col-xs-6', [
              '<h4>' . _('Title') . '</h4>',
              '<p class="lead">' . ($shift['URL'] != '' ? '<a href="' . $shift['URL'] . '">' . $shift['title'] . '</a>' : $shift['title']) . '</p>' 
          ]),
          div('col-sm-3 col-xs-6', [
              '<h4>' . _('Start') . '</h4>',
              '<p class="lead">',
              date('y-m-d', $shift['start']),
              '<br />',
              date('H:i', $shift['start']),
              '</p>' 
          ]),
          div('col-sm-3 col-xs-6', [
              '<h4>' . _('End') . '</h4>',
              '<p class="lead">',
              date('y-m-d', $shift['end']),
              '<br />',
              date('H:i', $shift['end']),
              '</p>' 
          ]),
          div('col-sm-3 col-xs-6', [
              '<h4>' . _('Location') . '</h4>',
              '<p class="lead">' . $room['Name'] . '</p>' 
          ]) 
      ]),
      div('row', [
          div('col-sm-6', [
              '<h2>' . _('Needed angels') . '</h2>',
              '<div class="list-group">' . $needed_angels . '</div>' 
          ]),
          div('col-sm-6', [
              '<h2>' . _('Description') . '</h2>',
              $parsedown->parse($shifttype['description']) 
          ]) 
      ]) 
  ]);
}

/**
 * Calc shift length in format 12:23h.
 *
 * @param Shift $shift          
 */
function shift_length($shift) {
  $length = floor(($shift['end'] - $shift['start']) / (60 * 60)) . ":";
  $length .= str_pad((($shift['end'] - $shift['start']) % (60 * 60)) / 60, 2, "0", STR_PAD_LEFT) . "h";
  return $length;
}
?>
