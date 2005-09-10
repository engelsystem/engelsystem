<?

$title = "Index";
$header = "Index";

include ("./inc/db.php");
include ("./inc/crypt.php");

session_start(); // alte Session - falls vorhanden - wiederherstellen...

if (!IsSet($_SESSION['UID'])) {

	$sql = "select * from User where Nick = '$user'";

	$userstring = mysql_query($sql, $con);

	// anzahl zeilen
	$user_anz  = mysql_num_rows($userstring);


	if ($user_anz == 1) { // Check, ob User angemeldet wird...
		if (mysql_result($userstring, 0, "Passwort") == PassCrypt($password)) { // Passwort ok...
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
			$_SESSION['Size'] = mysql_result($userstring, 0, "Size");
			$_SESSION['Gekommen'] = mysql_result($userstring, 0, "Gekommen");
			$_SESSION['Aktiv'] = mysql_result($userstring, 0, "Aktiv");
			$_SESSION['Tshirt'] = mysql_result($userstring, 0, "Tshirt");
			$_SESSION['color'] = mysql_result($userstring, 0, "color");
			$_SESSION['Avatar'] = mysql_result($userstring, 0, "Avatar");
			$_SESSION['Sprache'] = mysql_result($userstring, 0, "Sprache");
			$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
		
			// CVS import Data
			$SQL = "SELECT * FROM `UserCVS` WHERE UID=".$_SESSION['UID'];
			$Erg_CVS =  mysql_query($SQL, $con);
			$_SESSION['CVS'] = mysql_fetch_array($Erg_CVS);
	  
			include ("./inc/header.php");

			echo Get_Text(1).$_SESSION['Nick'];
			echo nl2br(Get_Text(2));
	
			include ("./news_output.php");
	      
		} 
		else 
		{ // Passwort nicht ok...

			include ("./inc/header.php");
	  
			echo Get_Text("pub_index_pass_no_ok");
			die();
			include ("./inc/login_eingabefeld.php");

		} // Ende Passwort-Check
	
	} 
	else 
	{ // Anzahl der User in User-Tabelle <> 1 --> keine Anmeldung
		include ("./inc/header.php");
		if ($user_anz == 0) 
		{
	  		echo Get_Text("pub_index_User_unset");
		} 
		else 
		{
			echo Get_Text("pub_index_User_more_as_one");
		}

		include ("./inc/login_eingabefeld.php");
	} // Ende Check, ob User angemeldet wurde
} 
else 
{ // User ist bereits angemeldet... normaler Inhalt...

	include ("./inc/header.php");
        echo Get_Text(1).$_SESSION['Nick'];
        echo nl2br(Get_Text(2));
		    
	include ("./news_output.php");

} // Ende Ueberpruefung, ob User bereits angemeldet...
	


?>
<!-- <br>
<a href="nonpublic/list.php"> list.php </a> -->
<?
include ("./inc/footer.php");



?>


