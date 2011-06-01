<?php
require_once ('bootstrap.php');
require_once ('includes/sys_auth.php');
require_once ('includes/sys_counter.php');
require_once ('includes/sys_lang.php');
require_once ('includes/sys_menu.php');
require_once ('includes/sys_mysql.php');
require_once ('includes/sys_page.php');
require_once ('includes/sys_template.php');
require_once ('includes/sys_user.php');

require_once ('config/config.php');
require_once ('config/config_db.php');

session_start();

sql_connect($config['host'], $config['user'], $config['pw'], $config['db']);

load_auth();

// Gewünschte Seite/Funktion
$p = "start";
if (isset ($_REQUEST['p']))
	$p = $_REQUEST['p'];

$title = Get_Text($p);
$content = "";

// Recht dafür vorhanden?
if (in_array($p, $privileges)) {
	if ($p == "news") {
		require_once ('includes/pages/user_news.php');
		$content = user_news();
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
		header("Location: " . page_link_to("login"));
	}
}

echo template_render('../templates/layout.html', array (
	'theme' => isset ($user) ? $user['color'] : $default_theme,
	'title' => $title,
	'menu' => make_menu(),
	'content' => $content
));

counter();
?>
