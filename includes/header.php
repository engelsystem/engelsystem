<?PHP 
include ("header_start.php");

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
?>
<HTML>
<HEAD>
<?PHP

echo "<TITLE>--- $title ---</TITLE>\n";
?>
<meta name="keywords" content="Engel, Himmelsverwaltung">
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="expires" content="0">
<meta name="robots" content="index">
<meta name="revisit-after" content="1 days">
<meta http-equiv="content-language" content="de">
<script type="text/javascript" src="<?PHP echo $url. $ENGEL_ROOT; ?>/css/grossbild.js"></script>
<link rel=stylesheet type="text/css" href="<?PHP echo $url. $ENGEL_ROOT; ?>css/style<?PHP
	if (!IsSet($_SESSION['color'])) 
		echo "6"; 
	else 
		echo $_SESSION['color'];
	?>.css">
<?PHP
if (isset($reload)) 
{
	if ($reload=="") 
	{
		$reload=3330;
	}	
	echo "\n<meta http-equiv=\"refresh\" content=\"".$reload.
	"; URL=./?reload=".$reload."\">\n";
}

if (isset($Page["AutoReload"])) 
{
	echo "\n<meta http-equiv=\"refresh\" content=\"". $Page["AutoReload"].
		"; URL=". $url. $ENGEL_ROOT. $Page["Name"]."\">\n";
}

echo "</HEAD>\n";

/////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// B O D Y
/////////////////////////////////////////////////////////////////////////////////////////////
echo "<BODY>\n";

echo "<div name=\"topic\" align=\"center\">\n\n";

if( isset($_SESSION['color']) && ($_SESSION['color']==6) )
{
	echo 	"\t<a name=\"#top\"><img src=\"". $url. $ENGEL_ROOT. "pic/himmel_w.png\" alt=\"Unser Himmel\"></a>\n";
}
else
{
	echo 	"\t<a name=\"#top\"><img src=\"". $url. $ENGEL_ROOT. "pic/himmel.png\" alt=\"Unser Himmel\"></a>\n";
}
echo "</div>\n\n";


//ausgabe new message
if( isset($_SESSION['CVS']["nonpublic/messages.php"]))
{
    if( $_SESSION['CVS']["nonpublic/messages.php"] == "Y")
    {
	$SQL = "SELECT `Datum` FROM `Messages` WHERE `RUID`=". $_SESSION["UID"]. " AND `isRead`='N'";
	$erg = mysql_query($SQL, $con);
	if( mysql_num_rows( $erg ) > 0 )
		echo "<br><a href=\"". $url. $ENGEL_ROOT.
			"nonpublic/messages.php\">". Get_Text("pub_messages_new1").
			" ".  mysql_num_rows( $erg ). " ".
			Get_Text("pub_messages_new2"). "</a><br><br>";
    }
}
?>
<table width="95%" align="center" border="0" cellpadding="7" cellspacing="0">
	<tr>
<?PHP
//ausgaeb Menu
if( !isset($_SESSION['Menu']))		$_SESSION['Menu'] = "L";
if( $_SESSION['Menu'] =="L")		include("menu.php");
?>

		<td valign="top" align="center">
<table border="0" width="100%" align="center" class="border" cellpadding="5" cellspacing="1">
	<tr class="contenttopic">
		<td>
<?PHP
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


function SetHeaderGo2Back ()
{
	global $BACKUP_SESSION_OLDURL;
	$_SESSION['oldurl'] = $BACKUP_SESSION_OLDURL;
}


if ( $Page["CVS"] != "Y" ) 
{
        echo "Du besitzt kein Rechte für diesen Bereich.<br>\n";
        If (IsSet($_SESSION['oldurl'])) 
		echo "<a href=\"". $_SESSION["oldurl"]. "\">".Get_Text("back")."</a> geht's zur&uuml;ck...\n";
	else
		echo "<a href=\"". $url. $ENGEL_ROOT. "\">".Get_Text("back")."</a> geht's zur&uuml;ck...\n";
        exit ();
}
?>


<!-- ende des header parts //-->



