<?PHP

function funktion_db_list($Table_Name) 
{
	global $con;

	$SQL = "SELECT * FROM `".$Table_Name."`";
	$Erg = mysql_query($SQL, $con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	$Anzahl_Felder = mysql_num_fields($Erg);

	echo "<table class=\"border\" cellpadding=\"2\" cellspacing=\"1\">";
	echo "<caption>DB: $Table_Name</caption>";

	echo "<tr class=\"contenttopic\">";
	for ($m = 0 ; $m < $Anzahl_Felder ; $m++)
	{
		echo "<th>". mysql_field_name($Erg, $m). "</th>";
	}
	echo "</tr>";

	for ($n = 0 ; $n < $Zeilen ; $n++)
	{
		echo "<tr class=\"content\">";
		for ($m = 0 ; $m < $Anzahl_Felder ; $m++)
		{
			echo "<td>".mysql_result($Erg, $n, $m). "</td>"; 
		}
		echo "</tr>";
	}
	echo "</table>";
}

function funktion_db_element_list_2row( $TopicName, $SQL) 
{
	global $con;

	echo "<table class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "<caption>$TopicName</caption>";
#	echo "\t<tr class=\"contenttopic\"> <td><h1>$TopicName</h1></td> </tr>\n";

	$Erg = mysql_query($SQL, $con);
	echo mysql_error($con);
	
	echo "<tr class=\"contenttopic\">";
	for ($m = 0 ; $m < mysql_num_fields($Erg) ; $m++)
	{
		echo "<th>". mysql_field_name($Erg, $m). "</th>";
	}
	echo "</tr>";

	for ($n = 0 ; $n < mysql_num_rows($Erg) ; $n++)
	{
		echo "<tr class=\"content\">";
		for ($m = 0 ; $m < mysql_num_fields($Erg) ; $m++)
		{
			echo "<td>".mysql_result($Erg, $n, $m). "</td>"; 
		}
		echo "</tr>";
	}
	echo "</table>\n";
}

?>
