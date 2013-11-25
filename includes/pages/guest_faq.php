<?php
function faq_title() {
  return _("FAQ");
}

function guest_faq() {
	$html = "";
	$faqs = sql_select("SELECT * FROM `FAQ`");
	foreach ($faqs as $faq) {
		$html .= "<dl>";
		if ($_SESSION['locale'] == "de_DE.UTF-8") {
			$html .= sprintf(
				'<dt>%s</dt> <dd>%s</dd>',
				$faq['Frage_de'],
				$faq['Antwort_de']
			);
		} else {
			$html .= sprintf(
				'<dt>%s</dt> <dd>%s</dd>',
				$faq['Frage_en'],
				$faq['Antwort_en']
			);
		}
		$html .= "</dl>";
	}
	return $html;
}
?>
