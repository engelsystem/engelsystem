<?PHP


function Get_Text ($TextID)
{
	GLOBAL $con;
	
	if( !isset($_SESSION['Sprache'])) 
		$_SESSION['Sprache'] = "EN";
	if( $_SESSION['Sprache']=="") 
		$_SESSION['Sprache']="EN";
	if( isset($_GET["SetLanguage"]))
		$_SESSION['Sprache']= $_GET["SetLanguage"];

	$SQL = "SELECT * FROM `Sprache` WHERE TextID=\"$TextID\" AND Sprache ='".$_SESSION['Sprache']."'";
	@$Erg = mysql_query($SQL, $con);
//	if(!mysql_error($con))
	if( mysql_num_rows( $Erg) == 1)
		return (@mysql_result($Erg, 0, "Text"));
	else
	{
//		die("Error Data, '$TextID' found ". mysql_num_rows( $Erg). "x");
		return "Error Data, '$TextID' found ". mysql_num_rows( $Erg). "x";
	}
	
}

function Print_Text ($TextID)
{
	GLOBAL $con;
	
	if( !isset($_SESSION['Sprache'])) 
		$_SESSION['Sprache'] = "EN";
	if( $_SESSION['Sprache']=="") 
		$_SESSION['Sprache']="EN";
	if( isset($_GET["SetLanguage"]))
		$_SESSION['Sprache']= $_GET["SetLanguage"];
	
	$SQL = "SELECT * FROM `Sprache` WHERE TextID=\"$TextID\" AND Sprache ='".$_SESSION['Sprache']."'";
	@$Erg = mysql_query($SQL, $con);

//	if(!mysql_error($con))
	if( mysql_num_rows( $Erg) == 1)
		echo nl2br(@mysql_result($Erg, 0, "Text"));
	else
	{
		echo "Error Data, '$TextID' found ". mysql_num_rows( $Erg). "x";
	}
}

?>
