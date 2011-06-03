<?php
function guest_faq() {
	$html = "";
	$faqs = sql_select("SELECT * FROM `FAQ`");
	foreach ($faqs as $faq) {
		$html .= "<dl>";
		if ($_SESSION['Sprache'] == "DE") {
			$html .= sprintf(
				'<dt>%s</dt> <dd>%s</dd>',
				$faq['frage_de'],
				$faq['antwort_de']
			);
		} else {
			$html .= sprintf(
				'<dt>%s</dt> <dd>%s</dd>',
				$faq['frage_en'],
				$faq['antwort_en']
			);
		}
		$html .= "</dl>";
	}
	return $html;
}
?>
