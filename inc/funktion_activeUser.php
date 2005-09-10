<?PHP

echo "<h4 class=\"menu\">Engels Online</h4>";

$SQL = "SELECT Nick, lastLogIn ".
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
	echo mysql_result( $Erg, $i, "Nick");
	$Tlog =	(substr( mysql_result( $Erg, $i, "lastLogIn"),  8, 2) * 60 * 60 * 24) +	// Tag
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 11, 2) * 60 * 60) +	// Stunde
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 14, 2) * 60) +		// Minute
		(substr( mysql_result( $Erg, $i, "lastLogIn"), 17, 2) );		// Sekunde
	
 	$Tlog = $Tist-$Tlog;
	echo " ". bcmod( $Tlog/60, 60). ":";
	if( strlen(bcmod( $Tlog, 60))==1)
		echo "0";
	echo bcmod( $Tlog, 60);
	echo "</li>\n";
}

?>
