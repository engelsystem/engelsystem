<?php
require_once realpath(__DIR__ . '/../includes/engelsystem_provider.php');

$free_pages = [
    'admin_event_config',
    'angeltypes',
    'api',
    'atom',
    'credits',
    'ical',
    'login',
    'rooms',
    'shifts',
    'shifts_json_export',
    'shifts_json_export_all',
    'stats',
    'users',
    'user_driver_licenses',
    'user_password_recovery' 
];

// Gewünschte Seite/Funktion
$page = "";
if (! isset($_REQUEST['p'])) {
  $_REQUEST['p'] = isset($user) ? "news" : "login";
}

if (isset($_REQUEST['p']) && preg_match("/^[a-z0-9_]*$/i", $_REQUEST['p']) && (in_array($_REQUEST['p'], $free_pages) || in_array($_REQUEST['p'], $privileges))) {
  $page = $_REQUEST['p'];
  
  $title = $page;
  $content = "";
  
  if ($page == "api") {
    require_once realpath(__DIR__ . '/../includes/controller/api.php');
    error("Api disabled temporily.");
    redirect(page_link_to('login'));
    api_controller();
  } elseif ($page == "ical") {
    require_once realpath(__DIR__ . '/../includes/pages/user_ical.php');
    user_ical();
  } elseif ($page == "atom") {
    require_once realpath(__DIR__ . '/../includes/pages/user_atom.php');
    user_atom();
  } elseif ($page == "shifts_json_export") {
    require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
    shifts_json_export_controller();
  } elseif ($page == "shifts_json_export_all") {
    require_once realpath(__DIR__ . '/../includes/controller/shifts_controller.php');
    shifts_json_export_all_controller();
  } elseif ($page == "stats") {
    require_once realpath(__DIR__ . '/../includes/pages/guest_stats.php');
    guest_stats();
  } elseif ($page == "user_password_recovery") {
    require_once realpath(__DIR__ . '/../includes/controller/users_controller.php');
    $title = user_password_recovery_title();
    $content = user_password_recovery_controller();
  } elseif ($page == "angeltypes") {
    list($title, $content) = angeltypes_controller();
  } elseif ($page == "shifts") {
    list($title, $content) = shifts_controller();
  } elseif ($page == "users") {
    list($title, $content) = users_controller();
  } elseif ($page == "user_angeltypes") {
    list($title, $content) = user_angeltypes_controller();
  } elseif ($page == "user_driver_licenses") {
    list($title, $content) = user_driver_licenses_controller();
  } elseif ($page == "shifttypes") {
    list($title, $content) = shifttypes_controller();
  } elseif ($page == "admin_event_config") {
    list($title, $content) = event_config_edit_controller();
  } elseif ($page == "rooms") {
    list($title, $content) = rooms_controller();
  } elseif ($page == "news") {
    $title = news_title();
    $content = user_news();
  } elseif ($page == "news_comments") {
    require_once realpath(__DIR__ . '/../includes/pages/user_news.php');
    $title = user_news_comments_title();
    $content = user_news_comments();
  } elseif ($page == "user_meetings") {
    $title = meetings_title();
    $content = user_meetings();
  } elseif ($page == "user_myshifts") {
    $title = myshifts_title();
    $content = user_myshifts();
  } elseif ($page == "user_shifts") {
    $title = shifts_title();
    $content = user_shifts();
  } elseif ($page == "user_messages") {
    $title = messages_title();
    $content = user_messages();
  } elseif ($page == "user_questions") {
    $title = questions_title();
    $content = user_questions();
  } elseif ($page == "user_settings") {
    $title = settings_title();
    $content = user_settings();
  } elseif ($page == "login") {
    $title = login_title();
    $content = guest_login();
  } elseif ($page == "register") {
    $title = register_title();
    $content = guest_register();
  } elseif ($page == "logout") {
    $title = logout_title();
    $content = guest_logout();
  } elseif ($page == "admin_questions") {
    $title = admin_questions_title();
    $content = admin_questions();
  } elseif ($page == "admin_user") {
    $title = admin_user_title();
    $content = admin_user();
  } elseif ($page == "admin_arrive") {
    $title = admin_arrive_title();
    $content = admin_arrive();
  } elseif ($page == "admin_active") {
    $title = admin_active_title();
    $content = admin_active();
  } elseif ($page == "admin_free") {
    $title = admin_free_title();
    $content = admin_free();
  } elseif ($page == "admin_news") {
    require_once realpath(__DIR__ . '/../includes/pages/admin_news.php');
    $content = admin_news();
  } elseif ($page == "admin_rooms") {
    $title = admin_rooms_title();
    $content = admin_rooms();
  } elseif ($page == "admin_groups") {
    $title = admin_groups_title();
    $content = admin_groups();
  } elseif ($page == "admin_language") {
    require_once realpath(__DIR__ . '/../includes/pages/admin_language.php');
    $content = admin_language();
  } elseif ($page == "admin_import") {
    $title = admin_import_title();
    $content = admin_import();
  } elseif ($page == "admin_shifts") {
    $title = admin_shifts_title();
    $content = admin_shifts();
  } elseif ($page == "admin_log") {
    $title = admin_log_title();
    $content = admin_log();
  } elseif ($page == "credits") {
    require_once realpath(__DIR__ . '/../includes/pages/guest_credits.php');
    $title = credits_title();
    $content = guest_credits();
  } else {
    require_once realpath(__DIR__ . '/../includes/pages/guest_start.php');
    $content = guest_start();
  }
} else {
  // Wenn schon eingeloggt, keine-Berechtigung-Seite anzeigen
  if (isset($user)) {
    $title = _("No Access");
    $content = _("You don't have permission to view this page. You probably have to sign in or register in order to gain access!");
  } else {
    // Sonst zur Loginseite leiten
    redirect(page_link_to("login"));
  }
}

$event_config = EventConfig();

echo template_render('../templates/layout.html', [
    'theme' => isset($user) ? $user['color'] : $default_theme,
    'title' => $title,
    'atom_link' => ($page == 'news' || $page == 'user_meetings') ? '<link href="' . page_link_to('atom') . (($page == 'user_meetings') ? '&amp;meetings=1' : '') . '&amp;key=' . $user['api_key'] . '" type="application/atom+xml" rel="alternate" title="Atom Feed">' : '',
    'menu' => make_menu(),
    'content' => msg() . $content,
    'header_toolbar' => header_toolbar(),
    'faq_url' => $faq_url,
    'contact_email' => $contact_email,
    'locale' => locale(),
    'event_info' => EventConfig_info($event_config) . '<br />' 
]);

?>
