<?php

if( !isset($Page["Public"])) $Page["Public"]="N";

$Page["Name"] = substr( $_SERVER['PHP_SELF'], strlen($ENGEL_ROOT) );
if( isset( $_SESSION['CVS'][ $Page["Name"] ]))
	$Page["CVS"] = $_SESSION['CVS'][ $Page["Name"] ];
else
	$Page["CVS"] = "";

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
	
	if( $Page["Public"] == "Y")
		echo "<h3>Page is Public !!!</h3>";
	else
		echo "<h4>Page is non Public</h4>";
}

?>
