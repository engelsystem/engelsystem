<? 
include ("./inc/config.php");
include ("./inc/db.php");
include ("./inc/funktion_lang.php");
session_start(); 
include ("./inc/secure.php");
/*if ( (!IsSet($_SESSION['UID'])) && (strstr ($_SERVER['PHP_SELF'], "nonpublic") !="" ) ) {
	header("Location: https://".$_SERVER['HTTP_HOST'].$ENGEL_ROOT);
	exit ();
} // Ende Rechte f. Nonpublic'*/

if( !isset($_SESSION['IP'])) 
	$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];

if (IsSet($_SESSION['UID']) and ($_SESSION['IP'] <> $_SERVER['REMOTE_ADDR']))
{
	header("Location: https://".$_SERVER['HTTP_HOST'].$ENGEL_ROOT);
}

include ("./inc/UserCVS.php");


//UPdate LASTlogin
if( isset($_SESSION['UID']))
{
	$SQLlastLogIn = "UPDATE `User` SET ".
			"`lastLogIn` = '". gmdate("Y-m-j H:i:s", time()). "'".
			" WHERE `UID` = '". $_SESSION['UID']. "' LIMIT 1;";
	mysql_query ($SQLlastLogIn, $con);
}								  


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
?>
<HTML>
<HEAD>
<?
echo "<TITLE>--- $title  ---</TITLE>";
?>
<meta name="keywords" content="Engel, Himmelsverwaltung">
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="expires" content="0">
<meta name="robots" content="index">
<meta name="revisit-after" content="1 days">
<meta http-equiv="content-language" content="de">
<link rel=stylesheet type="text/css" href="./inc/css/style<? if (!IsSet($_SESSION['color'])) { echo "1"; } else { echo $_SESSION['color']; } ?>.css">
<?
if (isset($reload)) {
 if ($reload=="") $reload=3330;
 echo "\n<meta http-equiv=\"refresh\" content=\"".$reload.
      "; URL=./?reload=".$reload."\">\n";
 }

if (isset($Page["AutoReload"])) {
 echo "\n<meta http-equiv=\"refresh\" content=\"". $Page["AutoReload"].
      "; URL=". substr($url, 0, strlen($url)-1). $ENGEL_ROOT. $Page["Name"]."\">\n";
 }
?>
</HEAD>
<BODY>
<?
if( !isset($Page["ShowTabel"]) ) $Page["ShowTabel"]="Y";
if( $Page["ShowTabel"]=="Y" )
{
//############################### ShowTable Start ##############################

?>
	<div align="center">
	<a name="#top"><img src="./inc/himmel<? if( isset($_SESSION['color'])) 
						if ($_SESSION['color']==6) echo "_w"; ?>.png" alt="Unser Himmel"></a>
	<p>
<table width="95%" align="center" border="0" cellpadding="7" cellspacing="0">
	<tr>
		<td valign="top" align="center">
<table border="0" width="100%" align="center" class="border" cellpadding="5" cellspacing="1">
	<tr class="contenttopic">
		<td>
<?
		echo "\t<a name=\"#$header\" class=\"contenttopic\">";
		if( strlen( $header) == 0 )
			echo "\n\t<b>". Get_Text($Page["Name"]). "</b></a>\n";
		else
			echo "\n\t<b>$header</b></a>\n";

?>
		</td>
	</tr>
	<tr class="content">
	 	<td>
<br>
<?php 
echo "\n\n\n";
 
if (IsSet($_SESSION['UID'])) {
	if( isset($_SESSION['oldurl']))
		$BACKUP_SESSION_OLDURL = $_SESSION['oldurl'];
	if( isset($_SESSION['newurl']))
		$_SESSION['oldurl'] = $_SESSION['newurl'];
	$_SESSION['newurl'] = $_SERVER["REQUEST_URI"];
} 


//############################### ShowTable Start ##############################
}	/* if (ShowTabel....*/


function SetHeaderGo2Back ()
{
	global $BACKUP_SESSION_OLDURL;
	$_SESSION['oldurl'] = $BACKUP_SESSION_OLDURL;
}


if ( $Page["CVS"] != "Y" ) {
        echo "Du besitzt kein Rechte für diesen Bereich.<br>\n";
        If (IsSet($_SESSION['oldurl'])) 
		echo "<a href=\"".$oldurl."\">".Get_Text("back")."</a> geht's zur&uuml;ck...\n";
	else
		echo "<a href=\"../nonpublic\">".Get_Text("back")."</a> geht's zur&uuml;ck...\n";
        exit ();
}
?>


<!-- ende des header parts //-->



