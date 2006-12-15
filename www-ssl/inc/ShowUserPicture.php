<?PHP

include ("./inc/config.php");
include ("./inc/error_handler.php");
include ("./inc/config_db.php");
if( !isset($_SESSION))	session_start();
include ("./inc/secure.php");


// Parameter check
if( !isset($_GET["UID"]) )
	$_GET["UID"]= "-1";

$SQL= "SELECT * FROM `UserPicture` WHERE `UID`='". $_GET["UID"]. "'";
$res = mysql_query( $SQL, $con);

if( mysql_num_rows($res) == 1)
{
	//genügend rechte
	if( !isset($_SESSION['UID']) || $_SESSION['UID'] == -1)
	{
		header( "HTTP/1.0 403 Forbidden");
		die( "403 Forbidden");
	}
	
	// ist das bild sichtbar?
	if( (mysql_result($res, 0, "show")=="N") AND ($_SESSION['UID']!=$_GET["UID"]) )
	{
		$SQL= "SELECT * FROM `UserPicture` WHERE `UID`='-1'";
		$res = mysql_query( $SQL, $con);
		if( mysql_num_rows($res) != 1)
		{
			header( 'HTTP/1.0 404 Not Found');
			die( "404 Not Found");
		}
	}

	/// bild aus db auslesen
	$bild = mysql_result($res, 0, "Bild");
	
	// ausgabe bild
	header( "Accept-Ranges: bytes");
	header( "Content-Length: ". strlen($bild));
	header( "Content-type: ". mysql_result($res, 0, "ContentType"));
	echo $bild;
}
else
{
	header( 'HTTP/1.0 404 Not Found');
	die( "404 Not Found");
}

?>
