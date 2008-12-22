<?PHP 
ini_set( "session.gc_maxlifetime", "65535");

include ("config.php");
include ("error_handler.php");
include ("config_db.php");
include ("funktion_lang.php");
include ("funktion_faq.php"); //für noAnswer() im menu
include ("funktion_menu.php");
include ("funktion_user.php");

if( !isset($_SESSION)) 
	session_start(); 
include ("secure.php");

if( !isset($_SESSION['IP'])) 
	$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];

if (IsSet($_SESSION['UID']) and ($_SESSION['IP'] <> $_SERVER['REMOTE_ADDR']))
{
	session_destroy ();
	header("Location: $url". substr($ENGEL_ROOT,1) );
}

include ("UserCVS.php");


//UPdate LASTlogin
if( isset($_SESSION['UID']))
{
	$SQLlastLogIn = "UPDATE `User` SET ".
			"`lastLogIn` = '". gmdate("Y-m-j H:i:s", time()). "'".
			" WHERE `UID` = '". $_SESSION['UID']. "' LIMIT 1;";
	mysql_query ($SQLlastLogIn, $con);
}								  

?>
