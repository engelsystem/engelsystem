<?php
require_once ('bootstrap.php');
require_once ('includes/sys_auth.php');
require_once ('includes/sys_counter.php');
require_once ('includes/sys_lang.php');
require_once ('includes/sys_log.php');
require_once ('includes/sys_menu.php');
require_once ('includes/sys_mysql.php');
require_once ('includes/sys_page.php');
require_once ('includes/sys_shift.php');
require_once ('includes/sys_template.php');
require_once ('includes/sys_user.php');

require_once ('includes/model/LogEntries_model.php');
require_once ('includes/model/User_model.php');

require_once ('config/config.php');
require_once ('config/config_db.php');

require_once ('includes/pages/admin_questions.php');
require_once ('includes/pages/admin_user_angeltypes.php');
require_once ('includes/pages/user_messages.php');

session_start();

sql_connect($config['host'], $config['user'], $config['pw'], $config['db']);

load_auth();

// JSON Authorisierung gewünscht?
if (isset ($_REQUEST['auth']))
  json_auth_service();

// Gewünschte Seite/Funktion
$p = isset ($user) ? "news" : "login";
if (isset ($_REQUEST['p']) && preg_match("/^[a-z0-9_]*$/i", $_REQUEST['p']) && ($_REQUEST['p'] == 'stats' || (sql_num_query("SELECT * FROM `Privileges` WHERE `name`='" . sql_escape($_REQUEST['p']) . "' LIMIT 1") > 0)))
  $p = $_REQUEST['p'];

$title = Get_Text($p);
$content = "";

if ($p == "ical") {
  require_once ('includes/pages/user_ical.php');
  user_ical();
}
elseif ($p == "atom") {
  require_once ('includes/pages/user_atom.php');
  user_atom();
}
elseif ($p == "shifts_json_export") {
  require_once ('includes/controller/shifts_controller.php');
  shifts_json_export_controller();
}
elseif ($p == "stats") {
  require_once ('includes/pages/guest_stats.php');
  guest_stats();
}
// Recht dafür vorhanden?
elseif (in_array($p, $privileges)) {
  if ($p == "news") {
    require_once ('includes/pages/user_news.php');
    $content = user_news();
  }
  elseif ($p == "news_comments") {
    require_once ('includes/pages/user_news.php');
    $content = user_news_comments();
  }
  elseif ($p == "user_meetings") {
    require_once ('includes/pages/user_news.php');
    $content = user_meetings();
  }
  elseif ($p == "user_myshifts") {
    require_once ('includes/pages/user_myshifts.php');
    $content = user_myshifts();
  }
  elseif ($p == "user_shifts") {
    require_once ('includes/pages/user_shifts.php');
    $content = user_shifts();
  }
  elseif ($p == "user_messages") {
    $content = user_messages();
  }
  elseif ($p == "user_questions") {
    require_once ('includes/pages/user_questions.php');
    $content = user_questions();
  }
  elseif ($p == "user_wakeup") {
    require_once ('includes/pages/user_wakeup.php');
    $content = user_wakeup();
  }
  elseif ($p == "user_settings") {
    require_once ('includes/pages/user_settings.php');
    $content = user_settings();
  }
  elseif ($p == "login") {
    require_once ('includes/pages/guest_login.php');
    $content = guest_login();
  }
  elseif ($p == "register") {
    require_once ('includes/pages/guest_login.php');
    $content = guest_register();
  }
  elseif ($p == "logout") {
    require_once ('includes/pages/guest_login.php');
    $content = guest_logout();
  }
  elseif ($p == "admin_questions") {
    $content = admin_questions();
  }
  elseif ($p == "admin_user") {
    require_once ('includes/pages/admin_user.php');
    $content = admin_user();
  }
  elseif ($p == "admin_user_angeltypes") {
    require_once ('includes/pages/admin_user_angeltypes.php');
    $content = admin_user_angeltypes();
  }
  elseif ($p == "admin_arrive") {
    require_once ('includes/pages/admin_arrive.php');
    $content = admin_arrive();
  }
  elseif ($p == "admin_active") {
    require_once ('includes/pages/admin_active.php');
    $content = admin_active();
  }
  elseif ($p == "admin_free") {
    require_once ('includes/pages/admin_free.php');
    $content = admin_free();
  }
  elseif ($p == "admin_news") {
    require_once ('includes/pages/admin_news.php');
    $content = admin_news();
  }
  elseif ($p == "admin_angel_types") {
    require_once ('includes/pages/admin_angel_types.php');
    $content = admin_angel_types();
  }
  elseif ($p == "admin_rooms") {
    require_once ('includes/pages/admin_rooms.php');
    $content = admin_rooms();
  }
  elseif ($p == "admin_groups") {
    require_once ('includes/pages/admin_groups.php');
    $content = admin_groups();
  }
  elseif ($p == "admin_faq") {
    require_once ('includes/pages/admin_faq.php');
    $content = admin_faq();
  }
  elseif ($p == "admin_language") {
    require_once ('includes/pages/admin_language.php');
    $content = admin_language();
  }
  elseif ($p == "admin_import") {
    require_once ('includes/pages/admin_import.php');
    $content = admin_import();
  }
  elseif ($p == "admin_shifts") {
    require_once ('includes/pages/admin_shifts.php');
    $content = admin_shifts();
  }
  elseif ($p == "admin_log") {
    require_once ('includes/pages/admin_log.php');
    $content = admin_log();
  } else {
    require_once ('includes/pages/guest_start.php');
    $content = guest_start();
  }
}
elseif ($p == "credits") {
  require_once ('includes/pages/guest_credits.php');
  $content = guest_credits();
}
elseif ($p == "faq") {
  require_once ('includes/pages/guest_faq.php');
  $content = guest_faq();
} else {
  // Wenn schon eingeloggt, keine-Berechtigung-Seite anzeigen
  if (isset ($user)) {
    $title = Get_Text("no_access_title");
    $content = Get_Text("no_access_text");
  } else {
    // Sonst zur Loginseite leiten
    redirect(page_link_to("login"));
  }
}

// Hinweis für ungelesene Nachrichten
if (isset ($user) && $p != "user_messages")
  $content = user_unread_messages() . $content;

// Hinweis für Engel, die noch nicht angekommen sind
if(isset($user) && $user['Gekommen'] == 0)
  $content = error("You are not marked as arrived. Please go to heaven's desk, get your angel badge and/or tell them that you arrived already.", true) . $content;

// Erzengel Hinweis für unbeantwortete Fragen
if (isset ($user) && $p != "admin_questions")
  $content = admin_new_questions() . $content;

// Erzengel Hinweis für freizuschaltende Engeltypen
if (isset ($user) && $p != "admin_user_angeltypes")
  $content = admin_new_user_angeltypes() . $content;

echo template_render('../templates/layout.html', array (
  'theme' => isset ($user) ? $user['color'] : $default_theme,
  'title' => $title,
  'atom_link' => ($p == 'news' || $p == 'user_meetings')? '<link href="' . page_link_to('atom') . (($p == 'user_meetings')? '&amp;meetings=1' : '') . '&amp;key=' . $user['api_key'] . '" type="application/atom+xml" rel="alternate" title="Atom Feed">' : '',
  'menu' => make_menu(),
  'content' => $content
));

counter();
?>
