<?php

/**
 * Available T-Shirt sizes
 */
$tshirt_sizes = array (
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
 * Render a users avatar.
 * @param User $user
 * @return string
 */
function User_Avatar_render($user) {
  return '<div class="avatar">&nbsp;<img src="pic/avatar/avatar' . $user['Avatar'] . '.gif"></div>';
}

/**
 * Render a user nickname.
 * @param User $user_source
 * @return string
 */
function User_Nick_render($user_source) {
  global $user, $privileges;
  if($user['UID'] == $user_source['UID'] || in_array('user_shifts_admin', $privileges))
    return '<a href="' . page_link_to('user_myshifts') . '&amp;id=' . $user_source['UID'] . '">' . htmlspecialchars($user_source['Nick']) . '</a>';
  else
    return htmlspecialchars($user_source['Nick']);
}


?>