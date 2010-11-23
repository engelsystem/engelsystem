<?PHP

// Functionen gibt es nicht auf ellen rechern
if( !function_exists("bcdiv"))
{
	function bcdiv( $param1, $param2)
	{
		return floor( $param1 / $param2);
	}
}

if( !function_exists("bcmod"))
{
	function bcmod( $param1, $param2)
	{
		return $param1 - ( $param2 * bcdiv( $param1, $param2));
	}
}


echo "<h4 class=\"menu\">Engel online</h4>";

$SQL = "SELECT UID, Nick, lastLogIn ".
	"FROM User ".
	"WHERE (`lastLogIn` > '". gmdate("YmdHis", time()-(60*60)). "' AND NOT (UID=". $_SESSION['UID']. ")) ".
	"ORDER BY lastLogIn DESC;";

$Erg = mysql_query( $SQL, $con);
	

$Tist =	(gmdate("d", time()) * 60 * 60 * 24) +	// Tag
	(gmdate("H", time()) * 60 * 60) +	// Stunde
	(gmdate("i", time()) * 60) +		// Minute
	(gmdate("s", time()) ); 		// Sekunde

for( $i=0; $i<mysql_num_rows($Erg); $i++)
{
	echo "\t\t\t<li>";
	if( $_SESSION['UID']>0 )
		echo DisplayAvatar( mysql_result( $Erg, $i, "UID"));
	// Schow Admin Page
	echo funktion_isLinkAllowed_addLink_OrLinkText(
		"admin/userChangeNormal.php?enterUID=". mysql_result( $Erg, $i, "UID"). "&Type=Normal",
		mysql_result( $Erg, $i, "Nick"));

	$Tlog =	(substr( mysql_result( $Erg, $i, "lastLogIn"),  8, 2) * 60 * 60 * 24) +	// Tag
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 11, 2) * 60 * 60) +	// Stunde
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 14, 2) * 60) +		// Minute
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 17, 2) );		// Sekunde
	
 	$Tlog = $Tist-$Tlog;
	echo " ". bcdiv( $Tlog, 60). ":";
	if( strlen(bcmod( $Tlog, 60))==1)
		echo "0";
	echo bcmod( $Tlog, 60);
	echo "</li>\n";
}

?>
