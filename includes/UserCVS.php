<?php

if( !isset($_SESSION['UID'])) 
	$_SESSION['UID'] = -1;

// CVS import Data
$SQL_CVS = "SELECT * FROM `UserCVS` WHERE UID=".$_SESSION['UID'];
$Erg_CVS =  mysql_query($SQL_CVS, $con);
$_SESSION['CVS'] = mysql_fetch_array($Erg_CVS);


// Group import Data, if nesseary
if( isset( $_SESSION['CVS'][ "GroupID" ]))
{
	$SQL_GRP = "SELECT * FROM `UserCVS` WHERE UID=".$_SESSION['CVS'][ "GroupID" ];
	$Erg_GRP =  mysql_query($SQL_GRP, $con);
	$_SESSION['CVS_Group'] = mysql_fetch_array($Erg_GRP);
	
	foreach( $_SESSION['CVS'] as $k => $v)
	{
		if($v=="G") // Right == Group 
			$_SESSION['CVS'][$k] = $_SESSION['CVS_Group'][$k];
	}
}

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
//	foreach( $_SESSION as $k => $v)
//		echo "$k = $v<br>\n";
	echo "<pre>\$_SESSION:\n";
		print_r($_SESSION);
	echo "</pre>";

	if( strlen($Page["CVS"]) == 0 )
		echo "<h1><u> CVS ERROR, on page '". $Page["Name"]. "'</u></h1>";
	else
		echo "CVS: ". $Page["Name"]. " => '". $Page["CVS"]. "'<br>";
	
}

?>
