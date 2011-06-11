<?php
function guest_start() {
	require_once ('includes/pages/guest_login.php');
	$html = "<p>" . Get_Text("index_text1") . "</p>\n";
	$html .= "<p>" . Get_Text("index_text2") . "</p>\n";
	$html .= "<p>" . Get_Text("index_text3") . "</p>\n";

	$html .= guest_login_form();

	$html .= "<h6>" . Get_Text("index_text4") . "</h6>";
	return $html;
}
?>