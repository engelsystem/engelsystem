<?php
$title = "Himmel";
$header = "Schichtpl&auml;ne";
$submenus = 1;


if (!IsSet($_POST["action"])) 
{
	include ("./inc/header.php");
	include ("./inc/funktionen.php");
	include ("./inc/funktion_schichtplan.php");
	include ("./inc/funktion_user.php");
?>

Hallo <? echo $_SESSION['Nick']?>,<br>
auf dieser Seite kannst du dir den Schichtplan in einer Druckansicht generieren lassen. W&auml;hle hierf&uuml;r ein Datum und den Raum:
<br><br>
<form action="./schichtplan_druck.php" method="post" target="_print">
<input type="hidden" name="action" value="1">


<table>
	<tr>
		<td align="right">Datum:</td>
		<td align="left">
			<select name="ausdatum">
<?
$SQL = "SELECT DateS FROM `Shifts` ORDER BY 'DateS'";
$Erg = mysql_query($SQL, $con);
if (!isset($ausdatum)) 
	$ausdatum = substr(mysql_result($Erg, $i , 0), 0,10);

for ($i = 0 ; $i < mysql_fetch_row($Erg) ; $i++) 
{
	if ($tmp != substr(mysql_result($Erg, $i , 0), 0,10)) 
	{
		$tmp =  substr(mysql_result($Erg, $i , 0), 0,10);
		echo "\t\t\t\t<option value=\"$tmp\">$tmp</option>\n";
	}
} 

?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">Raum:</td>
		<td align="left">
			<select name="Raum">
<?php 

	$res = mysql_query("SELECT Name, RID FROM `Room` WHERE `show`!='N' ORDER BY Name;",$con);

	for ($i = 0; $i < mysql_num_rows($res); $i++) 
	{
		$rid=mysql_result($res,$i,"RID");
		$raum_name=mysql_result($res, $i, "Name");
		echo "\t\t\t\t<option value=\"$rid\">$raum_name</option>\n";
	}
?>
			</select>
		</td>
	</tr>
	
</table>
<br>
<input type="submit" value="generieren...">
</form>

<br><br>
<?
	include ("./inc/footer.php");
} 
else 	//#################################################################
{
   if (IsSet($_POST["Raum"]) AND IsSet($_POST["ausdatum"])) 
	{
   	$Raum = $_POST["Raum"];
	$ausdatum = $_POST["ausdatum"];

	include ("./inc/db.php");
	include ("./inc/config.php");
	include ("./inc/secure.php");
	//var wird nur gesetzt immer edit auszublenden, achtung sesion darf nicht gestart sein !!!
	$_SESSION['CVS'][ "admin/schichtplan.php" ] = "N";	
	include ("./inc/funktion_lang.php");
	include ("./inc/funktion_schichtplan.php");
	include ("./inc/funktion_user.php");
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
			<span style="font-weight:bold;font-size:200%"><? echo $ausdatum; ?></span>
		</td>
		<td width="350" align="right">
			<span style="font-weight:bold;font-size:100%">Raum:</span>
			<span style="font-weight:bold;font-size:200%"><? echo $RoomID[$Raum]; ?> </span>
		</td>
	</tr>
</table>

<table border="2" width="650" class="border" cellpadding="2" cellspacing="1">
<? 
//Ausgabe Spalten überschrift
?>
	<tr class="contenttopic">
		<th bgcolor="#E0E0E0">Uhrzeit</th>
		<th bgcolor="#E0E0E0">Schichtplanbelegung</th>
	</tr>
<?

//Zeit Ausgeben
for( $i = 0; $i < 24; $i++ )
	for( $j = 0; $j < $GlobalZeileProStunde; $j++)
	{
		$Spalten[$i * $GlobalZeileProStunde + $j] =
			"\t<tr class=\"content\">\n";
		if( $j==0)
		{
			$Spalten[$i * $GlobalZeileProStunde + $j].=
				"\t\t<td rowspan=\"$GlobalZeileProStunde\">";
			if( $i < 10 )
				$Spalten[$i * $GlobalZeileProStunde + $j].= "0";
			$Spalten[$i * $GlobalZeileProStunde + $j].= "$i:";
			if( ( ($j*60) / $GlobalZeileProStunde) < 10 )
				$Spalten[$i * $GlobalZeileProStunde + $j].= "0";
			$Spalten[$i * $GlobalZeileProStunde + $j].=
				( ($j*60) / $GlobalZeileProStunde). "</td>\n";
			
		}
	}


CreateRoomShifts( $Raum );


// Ausgabe Zeilen
	for ($i = 0; $i < (24 * $GlobalZeileProStunde); $i++) echo $Spalten[$i];
// Ende
echo "</table>\n";

echo "Stand: ". gmdate("Y-m-d H:i"). "\n";
echo "</body>\n";
echo "</html>\n";

} //isset($Vars)
} //isset($Action)

?>
