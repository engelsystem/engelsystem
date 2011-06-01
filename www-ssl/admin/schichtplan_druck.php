<?php
require_once ('../bootstrap.php');

$title = "Himmel";
$header = "Schichtpl&auml;ne";
$submenus = 1;

if (!IsSet ($_GET["action"])) {
	include ("includes/header.php");
	include ("includes/funktionen.php");
	include ("includes/funktion_schichtplan_aray.php");
	include ("includes/funktion_schichtplan.php");

	echo "Hallo " . $_SESSION['Nick'] . "<br />\n" .
	"auf dieser Seite kannst du dir den Schichtplan in einer Druckansicht generieren lassen. W&auml;hle hierf&uuml;r ein Datum und den Raum:\n" .
	"<br />\n";

	foreach ($VeranstaltungsTage as $k => $v) {

		$res = mysql_query("SELECT Name, RID FROM `Room` WHERE `show`!='N' ORDER BY `Name`;", $con);
		for ($i = 0; $i < mysql_num_rows($res); $i++) {
			$Tag = $VeranstaltungsTage[$k];
			$RID = mysql_result($res, $i, "RID");
			$Rname = mysql_result($res, $i, "Name");
			echo "\t<a href=\"./schichtplan_druck.php?action=1&Raum=$RID&ausdatum=$Tag\" target=\"_blank\">$Tag $Rname</a><br />\n";
		}
		echo "<br />\n";
	}
	echo "<br /><br />";

	include ("includes/footer.php");
} else //#################################################################
	{
	if (IsSet ($_GET["Raum"]) AND IsSet ($_GET["ausdatum"])) {
		$Raum = $_GET["Raum"];
		$ausdatum = $_GET["ausdatum"];

		include ("config/config_db.php");
		include ("config/config.php");
		include ("includes/secure.php");
		//var wird nur gesetzt immer edit auszublenden, achtung sesion darf nicht gestart sein !!!
		$_SESSION['CVS']["admin/schichtplan.php"] = "N";
		include ("includes/funktion_lang.php");
		include ("includes/funktion_schichtplan.php");
		include ("includes/funktion_schichtplan_aray.php");
		include ("includes/funktion_user.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Schichtplan</title>
<meta name="keywords" content="Engel, Himmelsverwaltung">
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="expires" content="0">
<meta name="robots" content="index">
<meta name="revisit-after" content="1 days">
<meta http-equiv="content-language" content="de">
</head>
<body>

<h1>Schichtplan</h1>

<table>
  <tr>
    <td width="250" align="left">
      <span style="font-weight:bold;font-size:100%">Datum:</span> 
      <span style="font-weight:bold;font-size:200%"><?php echo $ausdatum; ?></span>
    </td>
    <td width="350" align="right">
      <span style="font-weight:bold;font-size:100%">Raum:</span>
      <span style="font-weight:bold;font-size:200%"><?php echo $RoomID[$Raum]; ?> </span>
    </td>
  </tr>
</table>

<table border="2" width="650" class="border" cellpadding="2" cellspacing="1">
 
<!--Ausgabe Spalten �berschrift-->

  <tr class="contenttopic">
    <th bgcolor="#E0E0E0">Uhrzeit</th>
    <th bgcolor="#E0E0E0">Schichtplanbelegung</th>
  </tr>
<?php


		//Zeit Ausgeben
		for ($i = 0; $i < 24; $i++)
			for ($j = 0; $j < $GlobalZeileProStunde; $j++) {
				$Spalten[$i * $GlobalZeileProStunde + $j] = "\t<tr class=\"content\">\n";
				if ($j == 0) {
					$Spalten[$i * $GlobalZeileProStunde + $j] .= "\t\t<td rowspan=\"$GlobalZeileProStunde\">";
					if ($i < 10)
						$Spalten[$i * $GlobalZeileProStunde + $j] .= "0";
					$Spalten[$i * $GlobalZeileProStunde + $j] .= "$i:";
					if ((($j * 60) / $GlobalZeileProStunde) < 10)
						$Spalten[$i * $GlobalZeileProStunde + $j] .= "0";
					$Spalten[$i * $GlobalZeileProStunde + $j] .= (($j * 60) / $GlobalZeileProStunde) . "</td>\n";

				}
			}

		CreateRoomShifts($Raum);

		// Ausgabe Zeilen
		for ($i = 0; $i < (24 * $GlobalZeileProStunde); $i++)
			echo $Spalten[$i];
		// Ende
		echo "</table>\n";

		echo "Stand: " . gmdate("Y-m-d H:i") . "\n";
		echo "</body>\n";
		echo "</html>\n";

	} //isset($Vars)
} //isset($Action)
?>
