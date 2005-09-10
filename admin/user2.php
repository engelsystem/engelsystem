<?PHP

$title = "User-Liste";
$header = "Index";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");
include ("./inc/crypt.php");

if (IsSet($action)) 
{
	
	function SQLExec( $SQL )
	{ 
		global $con;
	
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) {
			echo "&Auml;nderung wurde gesichert...\n";
		} else {
			echo "Fehler beim speichern...\n";
		}

	}

	SetHeaderGo2Back();
	echo "Gesendeter Befehl: $action<br>";

	switch ($action) {

	case "change":
		if (IsSet($enterUID))
		{
			if ($Type == "Normal")
			{
				$SQL = "UPDATE `User` SET ";
				$SQL.= " `Nick` = '$eNick', `Name` = '$eName', `Vorname` = '$eVorname', ".
					"`Telefon` = '$eTelefon', `Handy` = '$eHandy', `DECT` = '$eDECT', ".
					"`email` = '$eemail', `Size` = '$eSize', ".
					"`Gekommen`= '$eGekommen', `Aktiv`= '$eAktiv', ".
					"`Tshirt` = '$eTshirt' ";
				$SQL.= "WHERE `UID` = '$enterUID' LIMIT 1;";
				echo "User-";
				SQLExec( $SQL );
			}
			if ($Type == "Secure")
			{
				$SQL2 = "UPDATE `UserCVS` SET ";
			  	$SQL_CVS = "SELECT * FROM `UserCVS` WHERE UID=$enterUID";
				$Erg_CVS =  mysql_query($SQL_CVS, $con);
				$CVS_Data = mysql_fetch_array($Erg_CVS);
				$CVS_Data_i = 1;
				foreach ($CVS_Data as $CVS_Data_Name => $CVS_Data_Value) 
				{
					if( ($CVS_Data_i+1)%2 && $CVS_Data_Name!="UID")
						$SQL2.= "`$CVS_Data_Name` = '".$$CVS_Data_i."', ";
			    		$CVS_Data_i++;
					}
				$SQL2 = substr( $SQL2, 0, strlen($SQL2)-2 );
				$SQL2.= "  WHERE `UID` = '$enterUID' LIMIT 1;";
				echo "<br>Secure-";
				SQLExec( $SQL2 );
			}
		}
		break;

	case "delete":
		if (IsSet($enterUID))
		{
			$SQL="delete from `User` WHERE `UID` = '$enterUID' LIMIT 1;";
			SQLExec( $SQL );
			$SQL2="delete from `UserCVS` WHERE `UID` = '$enterUID' LIMIT 1;";
			SQLExec( $SQL2 );
			$SQL3="UPDATE `ShiftEntry` SET `UID` = '0', `Comment` = NULL ".
				"WHERE `UID` = '$enterUID' LIMIT 1;";
			SQLExec( $SQL3 );
		}
		break;


	case "newpw":
		echo "Bitte neues Kennwort f&uuml;r <b>";
		// Get Nick
		$USQL = "SELECT * FROM User where UID=$eUID";
		$Erg = mysql_query($USQL, $con);
		echo mysql_result($Erg, 0, "Nick");
		echo "</b> eingeben:<br>";
		echo "<form action=\"./user2.php\" method=\"POST\">\n";	
		echo "<input type=\"Password\" name=\"ePasswort\">";
		echo "<input type=\"Password\" name=\"ePasswort2\">";
		echo "<input type=\"hidden\" name=\"eUID\" value=\"$eUID\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"newpwsave\">\n";
	        echo "<input type=\"submit\" value=\"sichern...\">\n";
	        echo "</form>";
		break;

	case "newpwsave":
		if ($ePasswort == $ePasswort2) 
		{	// beide Passwoerter passen... 
			$ePasswort = PassCrypt($ePasswort);
			$SQL="UPDATE `User` SET `Passwort`='$ePasswort' where `UID` = '$eUID'";
			SQLExec( $SQL );
		} 
		else 
			echo "Das Passwort wurde nicht &uuml;bereinstimmend eingegeben!";
		break;
	} // end switch

// ende - Action ist gesetzt 
} 
else 
{
	// kein Action gesetzt -> abbruch
	echo "Unzul&auml;ssiger Aufruf. Bitte neu editieren...";
}

include ("./inc/footer.php");
?>

