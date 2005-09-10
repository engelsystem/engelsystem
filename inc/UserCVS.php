<?php


$Page["Name"] = substr( $_SERVER['PHP_SELF'], strlen($ENGEL_ROOT) );
$Page["CVS"] = $_SESSION['CVS'][ $Page["Name"] ];

if( $DEBUG ) 
{
	echo "UserID:". $_SESSION["UID"]. "<br>";
	echo "Nick:". $_SESSION["Nick"]. "<br>";
	
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
