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
    $toolbar_items[] = toolbar_item_link(page_link_to('register'), 'register', "Register", $p == 'register');
  
  if (in_array('user_myshifts', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('user_myshifts'), 'engel', $user['Nick'], $p == 'user_myshifts');
  
  if (in_array('user_settings', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('user_settings'), 'settings', "Settings", $p == 'user_settings');
  
  if (in_array('login', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('login'), 'login', "Login", $p == 'login');
  
  if (in_array('logout', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('logout'), 'logout', "Logout", $p == 'logout');
  
  return toolbar($toolbar_items);
}

function make_navigation() {
  global $p;
  global $privileges;
  $menu = "";
  
  $specials = array(
      "faq" 
  );
  
  $pages = array(
      "news",
      "user_meetings",
      "user_myshifts",
      "user_shifts",
      "user_messages",
      "user_questions",
      "user_wakeup",
      "admin_arrive",
      "admin_active",
      "admin_user",
      "admin_free",
      "admin_usershifts",
      "admin_questions",
      "admin_angel_types",
      "admin_user_angeltypes",
      "admin_shifts",
      "admin_rooms",
      "admin_groups",
      "admin_faq",
      "admin_language",
      "admin_import",
      "admin_log" 
  );
  
  foreach ($pages as $page)
    if (in_array($page, $privileges) || in_array($page, $specials))
      $menu .= '<li' . ($page == $p ? ' class="selected"' : '') . '><a href="' . page_link_to($page) . '">' . Get_Text($page) . '</a></li>';
  
  return '<nav><ul>' . $menu . '</ul></nav>';
}

function make_navigation_for($name, $pages) {
  global $privileges, $p;
  
  $specials = array(
      "faq" 
  );
  
  $menu = "";
  foreach ($pages as $page)
    if (in_array($page, $privileges) || in_array($page, $specials))
      $menu .= '<li' . ($page == $p ? ' class="selected"' : '') . '><a href="' . page_link_to($page) . '">' . Get_Text($page) . '</a></li>';
  
  if ($menu != "")
    $menu = '<nav class="container"><h4>' . $name . '</h4><ul class="content">' . $menu . '</ul></nav>';
  return $menu;
}

function make_menu() {
  return make_navigation() . make_langselect();
}

?>
