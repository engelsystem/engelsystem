<?php
function admin_user() {
	include ("includes/funktion_db_list.php");

	$html = "";
	// Userliste, keine UID uebergeben...

	$html .= "<a href=\"" . page_link_to("register") . "\">Neuen Engel eintragen &raquo;</a><br /><br />\n";

	if (!isset ($_GET["OrderBy"]))
		$_GET["OrderBy"] = "Nick";
	$SQL = "SELECT * FROM `User` ORDER BY `" . $_GET["OrderBy"] . "` ASC";
	$Erg = sql_query($SQL);

	// anzahl zeilen
	$Zeilen = mysql_num_rows($Erg);

	$html .= "Anzahl Engel: $Zeilen<br /><br />\n";
	$html .= '
	<table width="100%" class="border" cellpadding="2" cellspacing="1"> <thead>
	  <tr class="contenttopic">
	    <th>
	      <a href="' . page_link_to("admin_user") . '&OrderBy=Nick">Nick</a>
	    </th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Vorname">Vorname</a> <a href="' . page_link_to("admin_user") . '&OrderBy=Name">Name</a></th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Alter">Alter</a></th>
	    <th>
	      <a href="' . page_link_to("admin_user") . '&OrderBy=email">E-Mail</a>
	    </th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Size">Gr&ouml;&szlig;e</a></th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Gekommen">Gekommen</a></th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Aktiv">Aktiv</a></th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Tshirt">T-Shirt</a></th>
	    <th><a href="' . page_link_to("admin_user") . '&OrderBy=CreateDate">Registrier</a></th>
	    <th>&Auml;nd.</th>
	  </tr></thead>';
	$Gekommen = 0;
	$Active = 0;
	$Tshirt = 0;

	for ($n = 0; $n < $Zeilen; $n++) {
		$title = "";
		$user_groups = sql_select("SELECT * FROM `UserGroups` JOIN `Groups` ON (`Groups`.`UID` = `UserGroups`.`group_id`) WHERE `UserGroups`.`uid`=" . sql_escape(mysql_result($Erg, $n, "UID")) . " ORDER BY `Groups`.`Name`");
		$groups = array ();
		foreach ($user_groups as $user_group) {
			$groups[] = $user_group['Name'];
		}
		$title .= 'Groups: ' . join(", ", $groups) . "<br />";
		if (strlen(mysql_result($Erg, $n, "Telefon")) > 0)
			$title .= "Tel: " . mysql_result($Erg, $n, "Telefon") . "<br />";
		if (strlen(mysql_result($Erg, $n, "Handy")) > 0)
			$title .= "Handy: " . mysql_result($Erg, $n, "Handy") . "<br />";
		if (strlen(mysql_result($Erg, $n, "DECT")) > 0)
			$title .= "DECT: <a href=\"./dect.php?custum=" . mysql_result($Erg, $n, "DECT") . "\">" .
			mysql_result($Erg, $n, "DECT") . "</a><br />";
		if (strlen(mysql_result($Erg, $n, "Hometown")) > 0)
			$title .= "Hometown: " . mysql_result($Erg, $n, "Hometown") . "<br />";
		if (strlen(mysql_result($Erg, $n, "lastLogIn")) > 0)
			$title .= "Last login: " . date("Y-m-d H:i", mysql_result($Erg, $n, "lastLogIn")) . "<br />";
		if (strlen(mysql_result($Erg, $n, "Art")) > 0)
			$title .= "Type: " . mysql_result($Erg, $n, "Art") . "<br />";
		if (strlen(mysql_result($Erg, $n, "ICQ")) > 0)
			$title .= "ICQ: " . mysql_result($Erg, $n, "ICQ") . "<br />";
		if (strlen(mysql_result($Erg, $n, "jabber")) > 0)
			$title .= "jabber: " . mysql_result($Erg, $n, "jabber") . "<br />";

		$html .= "<tr class=\"content\">\n";
		$html .= "\t<td>" . mysql_result($Erg, $n, "Nick") . "</td>\n";
		$html .= "\t<td>" . mysql_result($Erg, $n, "Vorname") . " " . mysql_result($Erg, $n, "Name") . "</td>\n";
		$html .= "\t<td>" . mysql_result($Erg, $n, "Alter") . "</td>\n";
		$html .= "\t<td>";
		if (strlen(mysql_result($Erg, $n, "email")) > 0)
			$html .= "<a href=\"mailto:" . mysql_result($Erg, $n, "email") . "\">" .
			mysql_result($Erg, $n, "email") . "</a>";
		$html .= '<div class="hidden">' . $title . '</div>';
		$html .= "</td>\n";
		$html .= "\t<td>" . mysql_result($Erg, $n, "Size") . "</td>\n";
		$Gekommen += mysql_result($Erg, $n, "Gekommen");
		$html .= "\t<td>" . mysql_result($Erg, $n, "Gekommen") . "</td>\n";
		$Active += mysql_result($Erg, $n, "Aktiv");
		$html .= "\t<td>" . mysql_result($Erg, $n, "Aktiv") . "</td>\n";
		$Tshirt += mysql_result($Erg, $n, "Tshirt");
		$html .= "\t<td>" . mysql_result($Erg, $n, "Tshirt") . "</td>\n";
		$html .= "<td>" . mysql_result($Erg, $n, "CreateDate") . "</td>";
		$html .= "\t<td>" . '<a href="">Edit</a>' .
		"</td>\n";
		$html .= "</tr>\n";
	}
	$html .= "<tr>" .
	"<td></td><td></td><td></td><td></td><td></td>" .
	"<td>$Gekommen</td><td>$Active</td><td>$Tshirt</td><td></td><td></td></tr>\n";
	$html .= "\t</table>\n";
	// Ende Userliste

	$html .= "<hr /><h2>Statistics</h2>";
	$html .= funktion_db_element_list_2row("Hometown", "SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");

	$html .= "<br />\n";

	$html .= funktion_db_element_list_2row("Engeltypen", "SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");

	$html .= "<br />\n";

	$html .= funktion_db_element_list_2row("Used Groups", "SELECT Groups.Name AS 'GroupName', COUNT(Groups.Name) AS Count FROM `UserGroups` " .
	"LEFT JOIN `Groups` ON Groups.UID = UserGroups.group_id " .
	"WHERE (UserGroups.group_id!='NULL') " .
	"GROUP BY `GroupName` " .
	"");
	return $html;
}
?>