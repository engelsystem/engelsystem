<?php
function user_news() {
	return "<a href=\"#Neu\">" . Get_Text(3) . "</a>" . user_news_output();
}

function user_news_output() {
	global $DISPLAY_NEWS, $privileges;
	
	$html = "";

	if (isset ($_POST["text"]) && isset ($_POST["betreff"]) && IsSet ($_POST["date"])) {
		if (!isset ($_POST["treffen"]))
			$_POST["treffen"] = 0;
		$SQL = "INSERT INTO `News` (`Datum`, `Betreff`, `Text`, `UID`, `Treffen`) " .
		"VALUES ('" . sql_escape($_POST["date"]) . "', '" . sql_escape($_POST["betreff"]) . "', '" . sql_escape($_POST["text"]) . "', '" . sql_escape($_SESSION['uid']) .
		"', '" . sql_escape($_POST["treffen"]) . "');";
		$Erg = sql_query($SQL);
		if ($Erg == 1)
			$html .= Get_Text(4);
	}

	if (!IsSet ($_GET["news_begin"]))
		$_GET["news_begin"] = 0;

	if (!IsSet ($_GET["DISPLAY_NEWS"]))
		$_GET["DISPLAY_NEWS"] = 5;

	$SQL = "SELECT * FROM `News` ORDER BY `ID` DESC LIMIT " . intval($_GET["news_begin"]) . ", " . intval($_GET["DISPLAY_NEWS"]);
	$Erg = sql_query($SQL);

	// anzahl zeilen
	$news_rows = mysql_num_rows($Erg);

	for ($n = 0; $n < $news_rows; $n++) {

		if (mysql_result($Erg, $n, "Treffen") == 0)
			$html .= "<p class='question'>";
		else
			$html .= "<p class='engeltreffen'>";

		$html .= "<u>" . ReplaceSmilies(mysql_result($Erg, $n, "Betreff")) . "</u>\n";

		// Schow Admin Page
		if ($_SESSION['CVS']["admin/news.php"] == "Y")
			$html .= " <a href=\"./../admin/news.php?action=change&date=" . mysql_result($Erg, $n, "Datum") . "\">[edit]</a><br />\n\t\t";

		$html .= "<br />&nbsp; &nbsp;<font size=1>" . mysql_result($Erg, $n, "Datum") . ", ";
		$html .= UID2Nick(mysql_result($Erg, $n, "UID")) . "</font>";
		// avatar anzeigen?
		$html .= DisplayAvatar(mysql_result($Erg, $n, "UID"));
		$html .= "</p>\n";
		$html .= "<p class='answer'>" . ReplaceSmilies(nl2br(mysql_result($Erg, $n, "Text"))) . "</p>\n";
		$RefID = mysql_result($Erg, $n, "ID");
		$countSQL = "SELECT COUNT(*) FROM `news_comments` WHERE `Refid`='$RefID'";
		$countErg = sql_query($countSQL);
		$countcom = mysql_result($countErg, 0, "COUNT(*)");
		$html .= "<p class='comment' align='right'><a href=\"./news_comments.php?nid=$RefID\">$countcom comments</a></p>\n\n";
	}

	$html .= "<div align=\"center\">\n\n";
	$rowerg = sql_query("SELECT * FROM `News`");
	$rows = mysql_num_rows($rowerg);
	$dis_rows = round(($rows / $DISPLAY_NEWS) + 0.5);

	$html .= Get_Text(5);

	for ($i = 1; $i <= $dis_rows; $i++) {
		if (!((($i * $DISPLAY_NEWS) - $_GET["news_begin"]) == $DISPLAY_NEWS)) {
			$html .= '<a href="' . page_link_to("news") . '&news_begin=' . (($i * $DISPLAY_NEWS) - $DISPLAY_NEWS -1) . '">' . $i . '</a>&nbsp; ';
		} else {
			$html .= "$i&nbsp; ";
		}
	}
	$html .= '</div>
					<br /><hr />
					<h2>' . Get_Text(6) . '</h2>
					<a name="Neu">&nbsp;</a>
					
					<form action="" method="post">
					<?PHP
					
						// Datum mit uebergeben, um doppelte Eintraege zu verhindern 
						// (Reload nach dem Eintragen!)
					?>
					<input type="hidden" name="date" value="' . date("Y-m-d H:i:s") . '">
					<table>
					 <tr>
					  <td align="right">' . Get_Text(7) . '</td>
					  <td><input type="text" name="betreff" size="60"></td>
					 </tr>
					 <tr>
					  <td align="right">' . Get_Text(8) . '</td>
					  <td><textarea name="text" cols="50" rows="10"></textarea></td>
					 </tr>';
	if (in_array('news_add_meeting', $privileges)) {
		$html .= ' <tr>
										  <td align="right">' . Get_Text(9) . '</td>
										  <td><input type="checkbox" name="treffen" size="1" value="1"></td>
										 </tr>';

	}
	$html .= '</table>
					<br />
					<input type="submit" value="' . Get_Text("save") . '">
					</form>';
	return $html;
}
?>