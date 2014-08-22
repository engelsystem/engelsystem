<?php

function User_shift_mode_render($shift_mode) {
  if ($shift_mode == 0)
    return '<span class="text-success">' . _("Free") . '</span>';
  if ($shift_mode > 8 * 3600)
    return '<span class="text-success">' . sprintf(_("Next shift in %s min"), floor($shift_mode / 60)) . '</span>';
  if ($shift_mode > 0)
    return '<span class="text-warning">' . sprintf(_("Next shift in %s min"), floor($shift_mode / 60)) . '</span>';
  if ($shift_mode < 0)
    return '<span class="text-danger">' . sprintf(_("Current ends in %s min"), floor($shift_mode / 60)) . '</span>';
}

function User_view($user_source, $admin_user_privilege, $freeloader, $user_shift_mode, $user_angeltypes, $user_groups, $shifts, $its_me) {
  global $LETZTES_AUSTRAGEN, $privileges;
  
  $user_name = htmlspecialchars($user_source['Vorname']) . " " . htmlspecialchars($user_source['Name']);
  
  $myshifts_table = array();
  $html = "";
  $timesum = 0;
  foreach ($shifts as $shift) {
    $shift_info = $shift['name'];
    foreach ($shift['needed_angeltypes'] as $needed_angel_type) {
      $shift_info .= '<br><b>' . $needed_angel_type['name'] . ':</b> ';
      
      $shift_entries = array();
      foreach ($needed_angel_type['users'] as $user_source) {
        if ($its_me)
          $member = '<strong>' . User_Nick_render($user_source) . '</strong>';
        else
          $member = User_Nick_render($user_source);
        if ($user_source['freeloaded'])
          $member = '<strike>' . $member . '</strike>';
        
        $shift_entries[] = $member;
      }
      $shift_info .= join(", ", $shift_entries);
    }
    
    $myshift = array(
        'date' => date("Y-m-d", $shift['start']),
        'time' => date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']),
        'room' => $shift['Name'],
        'shift_info' => $shift_info,
        'comment' => $shift['Comment'] 
    );
    
    if ($shift['freeloaded']) {
      if (in_array("user_shifts_admin", $privileges))
        $myshift['comment'] .= '<br /><p class="error">' . _("Freeloaded") . ': ' . $shift['freeload_comment'] . '</p>';
      else
        $myshift['comment'] .= '<br /><p class="error">' . _("Freeloaded") . '</p>';
    }
    
    $myshift['actions'] = "";
    if ($its_me || in_array('user_shifts_admin', $privileges))
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . '&edit=' . $shift['id'] . '&id=' . $user_source['UID'], 'pencil', _("edit"));
    if (($shift['start'] > time() + $LETZTES_AUSTRAGEN * 3600) || in_array('user_shifts_admin', $privileges))
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . ((! $its_me) ? '&id=' . $id : '') . '&cancel=' . $shift['id'], 'cross', _("sign off"));
    
    if ($shift['freeloaded'])
      $timesum += - 2 * ($shift['end'] - $shift['start']);
    else
      $timesum += $shift['end'] - $shift['start'];
    $myshifts_table[] = $myshift;
  }
  if (count($myshifts_table) > 0)
    $myshifts_table[] = array(
        'date' => '<b>' . _("Sum:") . '</b>',
        'time' => "<b>" . round($timesum / (60 * 60), 1) . " h</b>",
        'room' => "",
        'shift_info' => "",
        'comment' => "",
        'actions' => "" 
    );
  
  return page_with_title('<span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . ' <small>' . $user_name . '</small>', array(
      msg(),
      div('row', array(
          div('col-md-3', array(
              '<h1>',
              '<span class="glyphicon glyphicon-phone"></span>',
              $user_source['DECT'],
              '</h1>' 
          )),
          div('col-md-3', array(
              '<h4>' . _("User state") . '</h4>',
              ($admin_user_privilege && $freeloader) ? '<span class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> ' . _("Freeloader") . '</span><br />' : '',
              $user_source['Gekommen'] ? User_shift_mode_render($user_shift_mode) . '<br />' : '',
              ($user_source['Gekommen'] ? '<span class="text-success"><span class="glyphicon glyphicon-home"></span> ' . _("Arrived") . '</span>' : '<span class="text-danger">' . _("Not arrived") . '</span>'),
              ($user_source['Gekommen'] && $admin_user_privilege && $user_source['Aktiv']) ? ' <span class="text-success">' . _("Active") . '</span>' : '',
              ($user_source['Gekommen'] && $admin_user_privilege && $user_source['Tshirt']) ? ' <span class="text-success">' . _("T-Shirt") . '</span>' : '' 
          )),
          div('col-md-3', array(
              '<h4>' . _("Angeltypes") . '</h4>',
              User_angeltypes_render($user_angeltypes) 
          )),
          div('col-md-3', array(
              '<h4>' . _("Rights") . '</h4>',
              User_groups_render($user_groups) 
          )) 
      )),
      $admin_user_privilege ? buttons(array(
          button(page_link_to('admin_user') . '&id=' . $user_source['UID'], '<span class="glyphicon glyphicon-edit"></span> ' . _("edit")) 
      )) : '',
      ($its_me || $admin_user_privilege) ? '<h2>' . _("Shifts") . '</h2>' : '',
      ($its_me || $admin_user_privilege) ? table(array(
          'date' => _("Day"),
          'time' => _("Time"),
          'room' => _("Location"),
          'shift_info' => _("Name &amp; workmates"),
          'comment' => _("Comment"),
          'actions' => _("Action") 
      ), $myshifts_table) : '',
      $its_me && count($shifts) == 0 ? error(sprintf(_("Go to the <a href=\"%s\">shifts table</a> to sign yourself up for some shifts."), page_link_to('user_shifts')), true) : '',
      ($its_me || $admin_user_privilege) ? '<h2>' . _("Exports") . '</h2>' : '',
      $its_me ? (sprintf(_("Export of shown shifts. <a href=\"%s\">iCal format</a> or <a href=\"%s\">JSON format</a> available (please keep secret, otherwise <a href=\"%s\">reset the api key</a>)."), page_link_to_absolute('ical') . '&key=' . $user_source['api_key'], page_link_to_absolute('shifts_json_export') . '&key=' . $user_source['api_key'], page_link_to('user_myshifts') . '&reset')) : '',
      (! $its_me && $admin_user_privilege) ? buttons(array(
          button(page_link_to_absolute('ical') . '&key=' . $user_source['api_key'], '<span class="glyphicon glyphicon-calendar"></span> ' . _("iCal Export")),
          button(page_link_to_absolute('shifts_json_export') . '&key=' . $user_source['api_key'], '<span class="glyphicon glyphicon-export"></span> ' . _("JSON Export")) 
      )) : '' 
  ));
}

/**
 * Available T-Shirt sizes
 */
$tshirt_sizes = array(
    '' => _("Please select..."),
    'S' => "S",
    'M' => "M",
    'L' => "L",
    'XL' => "XL",
    '2XL' => "2XL",
    '3XL' => "3XL",
    '4XL' => "4XL",
    '5XL' => "5XL",
    'S-G' => "S Girl",
    'M-G' => "M Girl",
    'L-G' => "L Girl",
    'XL-G' => "XL Girl" 
);

/**
 * View for password recovery step 1: E-Mail
 */
function User_password_recovery_view() {
  return page_with_title(user_password_recovery_title(), array(
      msg(),
      _("We will send you an e-mail with a password recovery link. Please use the email address you used for registration."),
      form(array(
          form_text('email', _("E-Mail"), ""),
          form_submit('submit', _("Recover")) 
      )) 
  ));
}

/**
 * View for password recovery step 2: New password
 */
function User_password_set_view() {
  return page_with_title(user_password_recovery_title(), array(
      msg(),
      _("Please enter a new password."),
      form(array(
          form_password('password', _("Password")),
          form_password('password2', _("Confirm password")),
          form_submit('submit', _("Save")) 
      )) 
  ));
}

function User_angeltypes_render($user_angeltypes) {
  $output = array();
  foreach ($user_angeltypes as $angeltype) {
    $class = "";
    if ($angeltype['restricted'] == 1)
      if ($angeltype['confirm_user_id'] != null)
        $class = 'text-success';
      else
        $class = 'text-warning';
    else
      $class = 'text-success';
    $output[] = '<span class="' . $class . '">' . ($angeltype['coordinator'] ? '<span class="glyphicon glyphicon-certificate"></span> ' : '') . $angeltype['name'] . '</span>';
  }
  return join('<br />', $output);
}

function User_groups_render($user_groups) {
  $output = array();
  foreach ($user_groups as $group) {
    $output[] = substr($group['Name'], 2);
  }
  return join('<br />', $output);
}

/**
 * Render a users avatar.
 *
 * @param User $user          
 * @return string
 */
function User_Avatar_render($user) {
  return '<div class="avatar">&nbsp;<img src="pic/avatar/avatar' . $user['Avatar'] . '.gif"></div>';
}

/**
 * Render a user nickname.
 *
 * @param User $user_source          
 * @return string
 */
function User_Nick_render($user_source) {
  global $user, $privileges;
  if ($user['UID'] == $user_source['UID'] || in_array('user_shifts_admin', $privileges))
    return '<a href="' . page_link_to('users') . '&amp;action=view&amp;user_id=' . $user_source['UID'] . '"><span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . '</a>';
  else
    return htmlspecialchars($user_source['Nick']);
}

?>