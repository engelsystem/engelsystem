<?php
function guest_faq() {
	$html = "";
	if ($_SESSION['Sprache'] == "DE") {
		$faqs = sql_select("SELECT * FROM `FAQ` WHERE `Sprache` = 'de'");
	} else {
		$faqs = sql_select("SELECT * FROM `FAQ` WHERE `Sprache` = 'en'");
	}

	foreach ($faqs as $faq) {
		$html .= "<dl>";
		$html .= sprintf(
			'<dt>%s</dt> <dd>%s</dd>',
			$faq['Frage'],
			$faq['Antwort']
		);
		$html .= "</dl>";
	}
	return $html;
}
?>
