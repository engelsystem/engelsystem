<?php
function page_link_to($page) {
	return '?p=' . $page;
}

function page_link_to_absolute($page) {
	return (isset ($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . preg_replace("/\?.*$/", '', $_SERVER['REQUEST_URI']) . page_link_to($page);
}

function make_navigation() {
	global $p;
	global $privileges;
	$menu_items = $privileges;
	$menu_items[] = "faq";
	$menu = "";

	// Standard Navigation
	$menu .= make_navigation_for(Get_Text('/'), array (
		"login",
		"logout",
		"register",
		"faq"
	));

	// Engel Navigation
	$menu .= make_navigation_for(Get_Text('inc_schicht_engel'), array (
		"news",
		"user_meetings",
		"user_myshifts",
		"user_shifts",
		"user_messages",
		"user_questions",
		"user_wakeup",
		"user_settings"
	));

	// Admin Navigation
	$menu .= make_navigation_for(Get_Text('admin/'), array (
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
	));
	return $menu;
}

function make_navigation_for($name, $pages) {
	global $privileges, $p;

	$specials = array (
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
	return make_navigation() . make_onlineusers() . make_langselect();
}

function make_onlineusers() {
	global $privileges, $user;
	$html = '<nav class="container online"><h4>Helfer online</h4>';

	$query = "SELECT UID, Nick, lastLogIn " . "FROM User " . "WHERE (`lastLogIn` > '" . (time() - 60 * 60) . "') " . "ORDER BY lastLogIn DESC";
	$users = sql_select($query);

	if (count($users) > 0) {
		$html .= "<ul class=\"content\">";

		foreach ($users as $online_user) {
			if (isset ($user) && $online_user['UID'] == $user['UID'])
				continue;

			$html .= "<li>";

			//if (isset ($user))
			//$html .= DisplayAvatar($online_user['UID']);

			// Show Admin Page
			if (in_array("admin_user_edit", $privileges)) {
				$html .= '<a href="admin/userChangeNormal.php?enterUID=' . $online_user['UID'] . '&Type=Normal">' . $online_user['Nick'] . '</a>';
			} else {
				$html .= $online_user['Nick'];
			}
			$last_action = time() - $online_user['lastLogIn'];

			$html .= " " . date("i:s", $last_action);
			$html .= "</li>\n";
		}

		$html .= "</ul>";
	} else {
		$html .= '<p class="content">Nobody...</p>';
	}
	$html .= '</nav>';
	return $html;
}

function make_langselect() {
	if (strpos($_SERVER["REQUEST_URI"], "?") > 0)
		$URL = $_SERVER["REQUEST_URI"] . "&SetLanguage=";
	else
		$URL = $_SERVER["REQUEST_URI"] . "?SetLanguage=";

	$html = '<p class="content"><a class="sprache" href="' . $URL . 'DE"><img src="pic/flag/de.png" alt="DE" title="Deutsch"></a>';
	$html .= '<a class="sprache" href="' . $URL . 'EN"><img src="pic/flag/en.png" alt="EN" title="English"></a></p>';
	return '<nav class="container"><h4>' . Get_Text("Sprache") . '</h4>' . $html . '</nav>';
}
?>
