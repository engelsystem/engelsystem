<?php

function page_link_to($page) {
  return '?p=' . $page;
}

function page_link_to_absolute($page) {
  return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . preg_replace("/\?.*$/", '', $_SERVER['REQUEST_URI']) . page_link_to($page);
}

/**
 * Renders the header toolbar containing search, login/logout, user and settings links.
 */
function header_toolbar() {
  global $p, $privileges, $user;
  
  $toolbar_items = array();
  
  if (in_array('register', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('register'), 'register', register_title(), $p == 'register');
  
  if (in_array('user_myshifts', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('user_myshifts'), 'engel', $user['Nick'], $p == 'user_myshifts');
  
  if (in_array('user_settings', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('user_settings'), 'settings', settings_title(), $p == 'user_settings');
  
  if (in_array('login', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('login'), 'login', login_title(), $p == 'login');
  
  if (in_array('logout', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('logout'), 'logout', logout_title(), $p == 'logout');
  
  return toolbar($toolbar_items);
}

function make_navigation() {
  global $p;
  global $privileges;
  $menu = "";
  
  $pages = array(
      "news" => news_title(),
      "user_meetings" => meetings_title(),
      "user_myshifts" => myshifts_title(),
      "user_shifts" => shifts_title(),
      "angeltypes" => angeltypes_title(),
      "user_messages" => messages_title(),
      "user_questions" => questions_title(),
      "user_wakeup" => wakeup_title(),
      "admin_arrive" => admin_arrive_title(),
      "admin_active" => admin_active_title(),
      "admin_user" => admin_user_title(),
      "admin_free" => admin_free_title(),
      "admin_questions" => admin_questions_title(),
      "admin_shifts" => admin_shifts_title(),
      "admin_rooms" => admin_rooms_title(),
      "admin_groups" => admin_groups_title(),
      "admin_import" => admin_import_title(),
      "admin_log" => admin_log_title()
  );
  
  foreach ($pages as $page => $title)
    if (in_array($page, $privileges))
      $menu .= '<li' . ($page == $p ? ' class="selected"' : '') . '><a href="' . page_link_to($page) . '">' . $title . '</a></li>';
  
  return '<nav><ul>' . $menu . '</ul></nav>';
}

function make_navigation_for($name, $pages) {
  global $privileges, $p;

  $menu = "";
  foreach ($pages as $page)
    if (in_array($page, $privileges))
      $menu .= '<li' . ($page == $p ? ' class="selected"' : '') . '><a href="' . page_link_to($page) . '">' . $title . '</a></li>';
  
  if ($menu != "")
    $menu = '<nav class="container"><h4>' . $name . '</h4><ul class="content">' . $menu . '</ul></nav>';
  return $menu;
}

function make_menu() {
  return make_navigation() . make_langselect();
}

?>
