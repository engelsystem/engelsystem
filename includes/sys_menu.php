<?php

function page_link_to($page) {
  if ($page == "")
    return '?';
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
  
  if (isset($user))
    $toolbar_items[] = toolbar_item_link(page_link_to('users') . '&amp;action=view', 'time', User_shift_state_render($user));
  
  if (! isset($user) && in_array('register', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('register'), 'plus', register_title(), $p == 'register');
  
  if (in_array('login', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('login'), 'log-in', login_title(), $p == 'login');
  
  if(isset($user) && in_array('user_messages', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('user_messages'), 'envelope', user_unread_messages());
  
  $user_submenu = make_langselect();
  $user_submenu[] = toolbar_item_divider();
  if (in_array('user_myshifts', $privileges))
    $toolbar_items[] = toolbar_item_link(page_link_to('users') . '&amp;action=view', ' icon-icon_angel', $user['Nick'], $p == 'users');
  
  if (in_array('user_settings', $privileges))
    $user_submenu[] = toolbar_item_link(page_link_to('user_settings'), 'list-alt', settings_title(), $p == 'user_settings');
  
  if (in_array('logout', $privileges))
    $user_submenu[] = toolbar_item_link(page_link_to('logout'), 'log-out', logout_title(), $p == 'logout');
  
  if (count($user_submenu) > 0)
    $toolbar_items[] = toolbar_dropdown('', '', $user_submenu);
  
  return toolbar($toolbar_items, true);
}

function make_navigation() {
  global $p, $privileges;
  
  $menu = array();
  $pages = array(
      "news" => news_title(),
      "user_meetings" => meetings_title(),
      "user_shifts" => shifts_title(),
      "angeltypes" => angeltypes_title(),
      "user_questions" => questions_title() 
  );
  
  foreach ($pages as $page => $title)
    if (in_array($page, $privileges))
      $menu[] = toolbar_item_link(page_link_to($page), '', $title, $page == $p);
  
  $admin_menu = array();
  $admin_pages = array(
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
  
  foreach ($admin_pages as $page => $title)
    if (in_array($page, $privileges))
      $admin_menu[] = toolbar_item_link(page_link_to($page), '', $title, $page == $p);
  
  if (count($admin_menu) > 0)
    $menu[] = toolbar_dropdown('', _("Admin"), $admin_menu);
  
  return toolbar($menu);
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
  return make_navigation();
}

?>
