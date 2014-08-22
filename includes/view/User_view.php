<?php

function User_view($user_source) {
  $user_name = htmlspecialchars($user_source['Vorname']) . " " . htmlspecialchars($user_source['Name']);
  return page_with_title('<span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . ' <small>' . $user_name . '</small>', array());
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
    return '<a href="' . page_link_to('user_myshifts') . '&amp;id=' . $user_source['UID'] . '"><span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . '</a>';
  else
    return htmlspecialchars($user_source['Nick']);
}

?>