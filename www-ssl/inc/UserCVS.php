<?php

if( !isset($_SESSION['UID'])) 
	$_SESSION['UID'] = -1;

// CVS import Data
$SQL = "SELECT * FROM `UserCVS` WHERE UID=".$_SESSION['UID'];
$Erg_CVS =  mysql_query($SQL, $con);
$_SESSION['CVS'] = mysql_fetch_array($Erg_CVS);

//pagename ermitteln
$Page["Name"] = substr( $_SERVER['PHP_SELF'], strlen($ENGEL_ROOT) );


//recht für diese seite auslesen
if( isset( $_SESSION['CVS'][ $Page["Name"] ]))
	$Page["CVS"] = $_SESSION['CVS'][ $Page["Name"] ];
else
{
	echo "SYSTEM ERROR: now right for ". $Page["Name"]. "exist";
	die;
}

if( $DEBUG ) 
{
//	echo "UserID:". $_SESSION["UID"]. "<br>";
//	echo "Nick:". $_SESSION["Nick"]. "<br>";

	foreach( $_SESSION as $k => $v)
		echo "$k = $v<br>\n";

	if( strlen($Page["CVS"]) == 0 )
		echo "<h1><u> CVS ERROR, on page '". $Page["Name"]. "'</u></h1>";
	else
		echo "CVS: ". $Page["Name"]. " => '". $Page["CVS"]. "'<br>";
	
}

?>
