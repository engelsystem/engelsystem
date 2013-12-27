<?php
require_once ('bootstrap.php');

require_once ('includes/mysql_provider.php');

require_once ('includes/sys_auth.php');
require_once ('includes/sys_counter.php');
require_once ('includes/sys_log.php');
require_once ('includes/sys_menu.php');
require_once ('includes/sys_page.php');
require_once ('includes/sys_template.php');

require_once ('includes/model/LogEntries_model.php');
require_once ('includes/model/NeededAngelTypes_model.php');
require_once ('includes/model/ShiftEntry_model.php');
require_once ('includes/model/Shifts_model.php');
require_once ('includes/model/User_model.php');

require_once ('includes/view/Questions_view.php');
require_once ('includes/view/Shifts_view.php');
require_once ('includes/view/ShiftEntry_view.php');
require_once ('includes/view/User_view.php');

require_once ('includes/helper/internationalization_helper.php');
require_once ('includes/helper/message_helper.php');
require_once ('includes/helper/error_helper.php');
require_once ('includes/helper/email_helper.php');

require_once ('config/config.default.php');
if (file_exists('../config/config.php'))
  require_once ('config/config.php');

require_once ('includes/pages/admin_active.php');
require_once ('includes/pages/admin_angel_types.php');
require_once ('includes/pages/admin_arrive.php');
require_once ('includes/pages/admin_free.php');
require_once ('includes/pages/admin_groups.php');
require_once ('includes/pages/admin_import.php');
require_once ('includes/pages/admin_log.php');
require_once ('includes/pages/admin_questions.php');
require_once ('includes/pages/admin_rooms.php');
require_once ('includes/pages/admin_shifts.php');
require_once ('includes/pages/admin_user.php');
require_once ('includes/pages/admin_user_angeltypes.php');
require_once ('includes/pages/guest_login.php');
require_once ('includes/pages/user_messages.php');
require_once ('includes/pages/user_myshifts.php');
require_once ('includes/pages/user_news.php');
require_once ('includes/pages/user_questions.php');
require_once ('includes/pages/user_settings.php');
require_once ('includes/pages/user_shifts.php');
require_once ('includes/pages/user_wakeup.php');

session_start();

gettext_init();

sql_connect($config['host'], $config['user'], $config['pw'], $config['db']);

load_auth();

// JSON Authorisierung gewünscht?
if (isset($_REQUEST['auth']))
  json_auth_service();

$free_pages = array(
    'stats',
    'shifts_json_export_all',
    'user_password_recovery' 
);

// Gewünschte Seite/Funktion
if (! isset($_REQUEST['p']))
  $_REQUEST['p'] = isset($user) ? "news" : "login";
if (isset($_REQUEST['p']) && preg_match("/^[a-z0-9_]*$/i", $_REQUEST['p']) && (in_array($_REQUEST['p'], $free_pages) || in_array($_REQUEST['p'], $privileges))) {
  $p = $_REQUEST['p'];
  
  $title = $p;
  $content = "";
  
  if ($p == "ical") {
    require_once ('includes/pages/user_ical.php');
    user_ical();
  } elseif ($p == "atom") {
    require_once ('includes/pages/user_atom.php');
    user_atom();
  } elseif ($p == "shifts_json_export") {
    require_once ('includes/controller/shifts_controller.php');
    shifts_json_export_controller();
  } elseif ($p == "shifts_json_export_all") {
    require_once ('includes/controller/shifts_controller.php');
    shifts_json_export_all_controller();
  } elseif ($p == "stats") {
    require_once ('includes/pages/guest_stats.php');
    guest_stats();
  } elseif ($p == "user_password_recovery") {
    require_once ('includes/controller/users_controller.php');
    $title = user_password_recovery_title();
    $content = user_password_recovery_controller();
  } elseif ($p == "news") {
    $title = news_title();
    $content = user_news();
  } elseif ($p == "news_comments") {
    require_once ('includes/pages/user_news.php');
    $title = user_news_comments_title();
    $content = user_news_comments();
  } elseif ($p == "user_meetings") {
    $title = meetings_title();
    $content = user_meetings();
  } elseif ($p == "user_myshifts") {
    $title = myshifts_title();
    $content = user_myshifts();
  } elseif ($p == "user_shifts") {
    $title = shifts_title();
    $content = user_shifts();
  } elseif ($p == "user_messages") {
    $title = messages_title();
    $content = user_messages();
  } elseif ($p == "user_questions") {
    $title = questions_title();
    $content = user_questions();
  } elseif ($p == "user_wakeup") {
    $title = wakeup_title();
    $content = user_wakeup();
  } elseif ($p == "user_settings") {
    $title = settings_title();
    $content = user_settings();
  } elseif ($p == "login") {
    $title = login_title();
    $content = guest_login();
  } elseif ($p == "register") {
    $title = register_title();
    $content = guest_register();
  } elseif ($p == "logout") {
    $title = logout_title();
    $content = guest_logout();
  } elseif ($p == "admin_questions") {
    $title = admin_questions_title();
    $content = admin_questions();
  } elseif ($p == "admin_user") {
    $title = admin_user_title();
    $content = admin_user();
  } elseif ($p == "admin_user_angeltypes") {
    $title = admin_user_angeltypes_title();
    $content = admin_user_angeltypes();
  } elseif ($p == "admin_arrive") {
    $title = admin_arrive_title();
    $content = admin_arrive();
  } elseif ($p == "admin_active") {
    $title = admin_active_title();
    $content = admin_active();
  } elseif ($p == "admin_free") {
    $title = admin_free_title();
    $content = admin_free();
  } elseif ($p == "admin_news") {
    require_once ('includes/pages/admin_news.php');
    $content = admin_news();
  } elseif ($p == "admin_angel_types") {
    $title = admin_angel_types_title();
    $content = admin_angel_types();
  } elseif ($p == "admin_rooms") {
    $title = admin_rooms_title();
    $content = admin_rooms();
  } elseif ($p == "admin_groups") {
    $title = admin_groups_title();
    $content = admin_groups();
  } elseif ($p == "admin_language") {
    require_once ('includes/pages/admin_language.php');
    $content = admin_language();
  } elseif ($p == "admin_import") {
    $title = admin_import_title();
    $content = admin_import();
  } elseif ($p == "admin_shifts") {
    $title = admin_shifts_title();
    $content = admin_shifts();
  } elseif ($p == "admin_log") {
    $title = admin_log_title();
    $content = admin_log();
  } elseif ($p == "credits") {
    require_once ('includes/pages/guest_credits.php');
    $title = credits_title();
    $content = guest_credits();
  } else {
    require_once ('includes/pages/guest_start.php');
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

if (isset($user)) {
  $freeloaded_shifts_count = count(ShiftEntries_freeloaded_by_user($user));
  if($freeloaded_shifts_count >= $max_freeloadable_shifts)
    $content = error(sprintf(_("You freeloaded %s shifts. Shift signup is locked. Please go to heavens desk to be unlocked again."), $freeloaded_shifts_count), true) . $content;
  
  // Hinweis für ungelesene Nachrichten
  if ($p != "user_messages")
    $content = user_unread_messages() . $content;
    
    // Hinweis für Engel, die noch nicht angekommen sind
  if ($user['Gekommen'] == 0)
    $content = error(_("You are not marked as arrived. Please go to heaven's desk, get your angel badge and/or tell them that you arrived already."), true) . $content;
  
  if ($enable_tshirt_size && $user['Size'] == "")
    $content = error(_("You need to specify a tshirt size in your settings!"), true) . $content;
  
  if ($user['DECT'] == "")
    $content = error(_("You need to specify a DECT phone number in your settings! If you don't have a DECT phone, just enter \"-\"."), true) . $content;
    
    // Erzengel Hinweis für unbeantwortete Fragen
  if ($p != "admin_questions")
    $content = admin_new_questions() . $content;
    
    // Erzengel Hinweis für freizuschaltende Engeltypen
  if ($p != "admin_user_angeltypes")
    $content = admin_new_user_angeltypes() . $content;
}

echo template_render('../templates/layout.html', array(
    'theme' => isset($user) ? $user['color'] : $default_theme,
    'title' => $title,
    'atom_link' => ($p == 'news' || $p == 'user_meetings') ? '<link href="' . page_link_to('atom') . (($p == 'user_meetings') ? '&amp;meetings=1' : '') . '&amp;key=' . $user['api_key'] . '" type="application/atom+xml" rel="alternate" title="Atom Feed">' : '',
    'menu' => make_menu(),
    'content' => $content,
    'header_toolbar' => header_toolbar(),
    'faq_url' => $faq_url 
));

counter();
?>
