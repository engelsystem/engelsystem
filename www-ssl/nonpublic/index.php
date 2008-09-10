<?PHP
$title = "Index";
$header = "Index";

include ("../../includes/config_db.php");
include ("../../includes/crypt.php");

session_start(); // alte Session - falls vorhanden - wiederherstellen...

function LoginOK()
{
	include ("../../includes/config.php");
	header("HTTP/1.1 302 Moved Temporarily");
	header("Location: ". substr($url, 0, strlen($url)-1). $ENGEL_ROOT. "nonpublic/news.php");
}

if ( !IsSet($_POST["user"]))
{ // User ist bereits angemeldet... normaler Inhalt...
	LoginOK();
} 
else
{ // User ist noch nicht angemeldet 
	$sql = "SELECT * FROM `User` WHERE `Nick`='". $_POST["user"]. "'";
	$userstring = mysql_query($sql, $con);

	// anzahl zeilen
	$user_anz  = mysql_num_rows($userstring);

	if ($user_anz == 1) { // Check, ob User angemeldet wird...
		if (mysql_result($userstring, 0, "Passwort") == PassCrypt($_POST["password"])) { // Passwort ok...
			// Session wird eingeleitet und Session-Variablen gesetzt..
			//  session_start();
			session_name("Himmel");
			$_SESSION['UID'] = mysql_result($userstring, 0, "UID");
			$_SESSION['Nick'] = mysql_result($userstring, 0, "Nick");
			$_SESSION['Name'] = mysql_result($userstring, 0, "Name");
			$_SESSION['Vorname'] = mysql_result($userstring, 0, "Vorname");
			$_SESSION['Alter'] = mysql_result($userstring, 0, "Alter");
			$_SESSION['Telefon'] = mysql_result($userstring, 0, "Telefon");
			$_SESSION['Handy'] = mysql_result($userstring, 0, "Handy");
			$_SESSION['DECT'] = mysql_result($userstring, 0, "DECT");
			$_SESSION['email'] = mysql_result($userstring, 0, "email");
			$_SESSION['ICQ'] = mysql_result($userstring, 0, "ICQ");
			$_SESSION['jabber'] = mysql_result($userstring, 0, "jabber");
			$_SESSION['Size'] = mysql_result($userstring, 0, "Size");
			$_SESSION['Gekommen'] = mysql_result($userstring, 0, "Gekommen");
			$_SESSION['Aktiv'] = mysql_result($userstring, 0, "Aktiv");
			$_SESSION['Tshirt'] = mysql_result($userstring, 0, "Tshirt");
			$_SESSION['Menu'] = mysql_result($userstring, 0, "Menu");
			$_SESSION['color'] = mysql_result($userstring, 0, "color");
			$_SESSION['Avatar'] = mysql_result($userstring, 0, "Avatar");
			$_SESSION['Sprache'] = mysql_result($userstring, 0, "Sprache");
			$_SESSION['Hometown'] = mysql_result($userstring, 0, "Hometown");
			$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
		
			// CVS import Data
			$SQL = "SELECT * FROM `UserCVS` WHERE `UID`='".$_SESSION['UID']."'";
			$Erg_CVS =  mysql_query($SQL, $con);
			$_SESSION['CVS'] = mysql_fetch_array($Erg_CVS);
			
			LoginOK();
		} 
		else 
		{ // Passwort nicht ok...
			$ErrorText = "pub_index_pass_no_ok";
		} // Ende Passwort-Check
	} 
	else 
	{ // Anzahl der User in User-Tabelle <> 1 --> keine Anmeldung
		if ($user_anz == 0) 
	  		$ErrorText = "pub_index_User_unset";
		else 
			$ErrorText = "pub_index_User_more_as_one";
	} // Ende Check, ob User angemeldet wurde
} 
include ("../../includes/header.php");
if( isset($ErrorText))
	echo "<h2>". Get_Text($ErrorText). "</h2><br>\n";
include ("../../includes/login_eingabefeld.php");
include ("../../includes/footer.php");

?>


