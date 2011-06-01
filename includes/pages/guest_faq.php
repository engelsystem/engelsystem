<?php
function guest_faq() {
	$html = "";
	$faqs = sql_select("SELECT * FROM `FAQ`");
	foreach ($faqs as $faq)
		if ($faq['Antwort'] != "") {
			list ($frage_de, $frage_en) = explode('<br />', $faq['Frage']);
			list ($antwort_de, $antwort_en) = explode('<br />', $faq['Antwort']);
			$html .= "<dl>";
			if ($_SESSION['Sprache'] == "DE") {
				$html .= "<dt>" . $frage_de . "</dt>";
				$html .= "<dd>" . $antwort_de . "</dd>";
			} else {
				$html .= "<dt>" . $frage_en . "</dt>";
				$html .= "<dd>" . $antwort_en . "</dd>";
			}
			$html .= "</dl>";
		}
	return $html;
}

function noAnswer() {
	global $con;

	$SQL = "SELECT UID FROM Questions WHERE `AID`='0'";
	$Res = mysql_query($SQL, $con);

	return mysql_num_rows($Res);
}
?>
