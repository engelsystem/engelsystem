<?PHP

$title = "Sprache";
$header = "Liste der existierenden Sprcheintr&auml;ge";
include ("./inc/header.php");


if( !isset( $_GET["TextID"] )  )
{
	echo Get_Text("Hello").$_SESSION['Nick'].", <br>\n";
	echo Get_Text("pub_sprache_text1")."<br><br>\n";

	echo "<a href=\"?ShowEntry=y\">". Get_Text("pub_sprache_ShowEntry"). "</a>";
	// ausgabe Tabellenueberschift
	$SQL_Sprachen = "SELECT `Sprache` FROM `Sprache` GROUP BY `Sprache`;";
	$erg_Sprachen = mysql_query($SQL_Sprachen, $con);
	echo mysql_error($con);
	
	for( $i=0; $i<mysql_num_rows( $erg_Sprachen ); $i++ )
		$Sprachen[mysql_result( $erg_Sprachen, $i, "Sprache" )] = $i;

	echo "\t<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n\t\t<tr>";
	echo "\t\t<td class=\"contenttopic\"><b>". Get_Text("pub_sprache_TextID"). "</b></td>";
	foreach( $Sprachen as $Name => $Value )
		echo "<td class=\"contenttopic\"><b>". 
			Get_Text("pub_sprache_Sprache"). " ". $Name.
			"</b></td>";
	echo "\t\t<td class=\"contenttopic\"><b>". Get_Text("pub_sprache_Edit"). "</b></td>";
	echo "\t\t</tr>";


	if( isset($_GET["ShowEntry"]))
	{
	// ausgabe eintraege
	$SQL = "SELECT * FROM `Sprache` ORDER BY `TextID`;";
	$erg = mysql_query($SQL, $con);
	echo mysql_error($con);

	$TextID_Old = mysql_result( $erg, 0, "TextID" );
	for( $i=0; $i<mysql_num_rows( $erg ); $i++ )
	{
		$TextID_New = mysql_result( $erg, $i, "TextID" );
		if( $TextID_Old != $TextID_New )
		{
			echo "<form action=\"sprache.php\">";
			echo "<tr class=\"content\">\n";
			echo "\t\t<td>$TextID_Old ".
			     "<input name=\"TextID\" type=\"hidden\" value=\"$TextID_Old\"> </td>\n";
		
			foreach( $Sprachen as $Name => $Value )
			{
				$Value = html_entity_decode( $Value, ENT_QUOTES);
				echo "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Value</textarea></td>\n";
				$Sprachen[ $Name ] = "";
			}
			
			echo "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
			echo "</tr>";
			echo "</form>\n";
			$TextID_Old = $TextID_New;
		}
		$Sprachen[ mysql_result( $erg, $i, "Sprache" ) ] = mysql_result( $erg, $i, "Text" );
	} /*FOR*/
	}
	
	//fuer neu eintraege
	echo "<form action=\"sprache.php\">";
	echo "<tr class=\"content\">\n";
	echo "\t\t<td><input name=\"TextID\" type=\"text\" size=\"40\" value=\"new\"> </td>\n";
		
	foreach( $Sprachen as $Name => $Value )
		echo "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Name Text</textarea></td>\n";

	echo "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
	echo "</tr>";
	echo "</form>\n";
	
	
	echo "</table>\n";
} /*if( !isset( $TextID )  )*/
else
{
	echo "edit: ". $_GET["TextID"]. "<br><br>";
	foreach ($_GET as $k => $v) {
		if( $k != "TextID" ) 
		{
			$sql_test = "SELECT * FROM `Sprache` ".
				"WHERE `TextID`='". $_GET["TextID"]. "' AND `Sprache`='$k'";
			$erg_test = mysql_query($sql_test, $con);

			if( mysql_num_rows($erg_test)==0 )
			{
				$sql_save = "INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) ".
					"VALUES ('". $_GET["TextID"]. "', '$k', '$v')";
				  echo $sql_save."<br>";
		        	$Erg = mysql_query($sql_save, $con);
			        if ($Erg == 1)
			                echo "\t $k Save: OK<br>\n";
			        else
		        	        echo "\t $k Save: KO<br>\n";
			}
			else if( mysql_result($erg_test, 0, "Text")!=$v )
			{
				$sql_save = "UPDATE `Sprache` SET `Text`='$v' ".
					"WHERE `TextID`='". $_GET["TextID"]. "' AND `Sprache`='$k' ";
				  echo $sql_save."<br>";
		        	$Erg = mysql_query($sql_save, $con);
			        if ($Erg == 1)
			                echo "\t $k Update: OK<br>\n";
			        else
		        	        echo "\t $k Update: KO<br>\n";
			}
			else
				echo "\t $k no changes<br>\n";
		}
	}
	   
}

include ("./inc/footer.php");
?>

