<?php
function guest_faq() {
	$html = "";
	$faqs = sql_select("SELECT * FROM `FAQ`");
	foreach ($faqs as $faq) {
		$html .= "<dl>";
		if ($_SESSION['Sprache'] == "DE") {
			$html .= "<dt>" . $faq['Frage_de'] . "</dt>";
			$html .= "<dd>" . $faq['Antwort_de'] . "</dd>";
		} else {
			$html .= "<dt>" . $faq['Frage_en'] . "</dt>";
			$html .= "<dd>" . $faq['Antwort_en'] . "</dd>";
		}
		$html .= "</dl>";
	}
	return $html;
}
?>
