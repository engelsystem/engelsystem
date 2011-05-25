<?PHP

$title = "User-Liste";
$header = "Index";
include ("../../../camp2011/includes/header.php");
include ("../../../camp2011/includes/funktion_db_list.php");
include ("../../../camp2011/includes/crypt.php");
include ("../../../camp2011/includes/funktion_db.php");

if( !IsSet($_POST["enterUID"]) ) 
{
	$Right = "N";
} elseif( $_POST["enterUID"] > 0 ) {
	$Right = $_SESSION['CVS'][ "admin/user.php"];
} else {
	$Right = $_SESSION['CVS'][ "admin/group.php"];
}

if ( ($Right=="Y") && IsSet($_GET["action"])) 
{
	SetHeaderGo2Back();
	echo "Gesendeter Befehl: ". $_GET["action"]. "<br>";
	
	switch ($_GET["action"]) 
	{
	case "change":
		if (IsSet($_POST["enterUID"]))
		{
			if ($_POST["Type"] == "Secure")
			{
				$SQL2 = "UPDATE `UserCVS` SET ";
			  	$SQL_CVS = "SELECT * FROM `UserCVS` WHERE `UID`='". $_POST["enterUID"]. "'";
				$Erg_CVS =  mysql_query($SQL_CVS, $con);
				$CVS_Data = mysql_fetch_array($Erg_CVS);
				$CVS_Data_i = 1;
				foreach ($CVS_Data as $CVS_Data_Name => $CVS_Data_Value) 
				{
					if( ($CVS_Data_i+1)%2 && $CVS_Data_Name!="UID") {
						if( $CVS_Data_Name == "GroupID")
						{
							if( $_POST["enterUID"] > 0 )
								$SQL2.= "`$CVS_Data_Name` = '". $_POST["GroupID"]."', ";
							else
								$SQL2.= "`$CVS_Data_Name` = NULL, ";
						} else {
							$SQL2.= "`$CVS_Data_Name` = '". $_POST[$CVS_Data_i]."', ";
						}
					}
			    		$CVS_Data_i++;
				}
				$SQL2 = substr( $SQL2, 0, strlen($SQL2)-2 );
				$SQL2.= "  WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1;";
				echo "<br>Secure-";
				$Erg = db_query($SQL2, "change user CVS");
				if ($Erg == 1) {
					echo "&Auml;nderung wurde gesichert...\n";
				} else {
					echo "Fehler beim speichern...\n(". mysql_error($con). ")";
				}
			}
			else
				echo "<h1>Fehler: Unbekanter Type (". $_POST["Type"]. ") übergeben\n</h1>\n";
		}
		else
			echo "<h1>Fehler: UserID (enterUID) wurde nicht per POST übergeben</h1>\n";
		break;

	case "changeGroupName":
		if (IsSet($_POST["enterUID"]) && ($_POST["enterUID"]<0) )
		{
			$SQL = "UPDATE `UserGroups` SET `Name`='". $_POST["GroupName"]. "' WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1 ;";
			$Erg = db_query($SQL, "Update Group Name");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
		}
		else
			echo "<h1>Fehler: UserID (enterUID) wurde nicht per POST übergeben</h1>\n";
		break;

	case "delete":
		if (IsSet($_POST["enterUID"]) && ($_POST["enterUID"]>0) )
		{
			echo "delate User...";
			$SQL="DELETE FROM `User` WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1;";
			$Erg = db_query($SQL, "User delete");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
			
			echo "<br>\ndelate UserCVS...";
			$SQL2="DELETE FROM `UserCVS` WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1;";
			$Erg = db_query($SQL2, "User CVS delete");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
			
			echo "<br>\ndelate UserEntry...";
			$SQL3="UPDATE `ShiftEntry` SET `UID`='0', `Comment`=NULL ".
				  "WHERE `UID`='". $_POST["enterUID"]. "';";
			$Erg = db_query($SQL3, "delate UserEntry");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
		} elseif (IsSet($_POST["enterUID"]) && ($_POST["enterUID"]<0) ) {
			echo "delate Group...";
			$SQL="DELETE FROM `UserGroups` WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1;";
			$Erg = db_query($SQL, "Group delete");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
			
			echo "<br>\ndelate UserCVS...";
			$SQL2="DELETE FROM `UserCVS` WHERE `UID`='". $_POST["enterUID"]. "' LIMIT 1;";
			$Erg = db_query($SQL2, "User CVS delete");
			if ($Erg == 1) {
				echo "&Auml;nderung wurde gesichert...\n";
			} else {
				echo "Fehler beim speichern...\n(". mysql_error($con). ")";
			}
			
		}
		break;
	} // end switch

// ende - Action ist gesetzt 
} elseif ( IsSet($_GET["new"]) &&  ($_SESSION['CVS']["admin/group.php"]=="Y") ) {
	echo "Gesendeter Befehl: ". $_GET["new"]. "<br>";
	
	switch ($_GET["new"]) 
	{
	case "newGroup":
		echo "\tGenerate new Group ID...\n";
		$SQLid="SELECT MIN(`UID`) FROM `UserCVS`;";
		$Erg = mysql_query( $SQLid);
		
		if( mysql_num_rows($Erg) == 1) {
			$NewId = mysql_result( $Erg, 0, 0)-1;
			$SQLnew1 = "INSERT INTO `UserGroups` (`UID`, `Name`) VALUES ('$NewId', '". $_POST["GroupName"]. "' );";
			$SQLnew2 = "INSERT INTO `UserCVS` (`UID`, `GroupID`) VALUES ('$NewId', NULL );";
			echo "\t<br>Generate new UserGroup ...\n";
			$ErgNew1 = db_query($SQLnew1, "create UserGroups Entry");
			if ($ErgNew1 == 1) 
			{
				echo "\t<br>Generate new User rights...\n";
				$ErgNew2 = db_query($SQLnew2, "UserCVS Entry");
				if ($ErgNew1 == 1) {
					echo "\t<br>New group was created.\n";
				} else {
					echo "Error on creation\n(". mysql_error($con). ")";
				}
			} else {
				echo "Error on creation\n(". mysql_error($con). ")";
			}

		}

		
		break;
	}
} else {
	// kein Action gesetzt -> abbruch
	echo "Unzul&auml;ssiger Aufruf.<br>Bitte neu editieren...";
}

include ("../../../camp2011/includes/footer.php");
?>

