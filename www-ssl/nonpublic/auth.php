<?PHP
header('Content-Type: application/json');

include ("../../includes/config.php");
include ("../../includes/config_db.php");

$User = $_POST['user'];
$Pass = $_POST['pw'];
$SourceOuth = $_POST['so'];

/*
$User = $_GET['user'];
$Pass = $_GET['pw'];
$SourceOuth = $_GET['so'];
*/

/*
$User = "admin";
$Pass = "21232f297a57a5a743894a0e4a801fc3"; // "admin";
$SourceOuth = 23;
*/

if ( 	isset($CurrentExternAuthPass) &&  
	($SourceOuth == $CurrentExternAuthPass) )
{ // User ist noch nicht angemeldet 
	$sql = "SELECT * FROM `User` WHERE `Nick`='". $User. "'";
	$Erg = mysql_query( $sql, $con);

	if ( mysql_num_rows( $Erg)  == 1) 
	{ // Check, ob User angemeldet wird...
		if (mysql_result( $Erg, 0, "Passwort") == $Pass)
		{ // Passwort ok...
			// Session wird eingeleitet und Session-Variablen gesetzt..
			$UID = mysql_result( $Erg, 0, "UID");

			// get CVS import Data
			$SQL = "SELECT * FROM `UserCVS` WHERE `UID`='". $UID. "'";
			$Erg_CVS =  mysql_query($SQL, $con);
			$CVS = mysql_fetch_array($Erg_CVS);
			
			$msg = array( 
					'status' => 'success',
					'rights' => $CVS
					);
			echo json_encode($msg);
			
		} 
		else 
		{
			echo json_encode(array('status' => 'failed'));
		}
	} 
	else 
	{
		echo json_encode(array('status' => 'failed'));
	}
} 
else 
{
	echo json_encode(array('status' => 'failed'));
}


?>


