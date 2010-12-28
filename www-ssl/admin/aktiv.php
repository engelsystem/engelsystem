<?PHP

$title = "akive Engel";
$header = "Liste der aktiven Engel";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");

echo "<form action=\"./aktiv.php\" method=\"post\">";
echo Get_Text("pub_aktive_Text1")."<br>\n";
echo Get_Text("pub_aktive_Text2")."<br><br>\n";
	
// auswahlbox
echo Get_Text("pub_aktive_Text31")."\n";
echo "<select name=\"Anzahl\">\n";
for( $i=0; $i<50; $i++) 
	echo "\t<option value=\"$i\">$i</option>\n";
echo "</select>";
echo Get_Text("pub_aktive_Text32")."<br><br>\n";
echo "<input type=\"submit\" name=\"SendType\" value=\"Show..\">\n";
echo "<input type=\"submit\" name=\"SendType\" value=\"Write..\">\n";
echo "</form>\n";

echo "<form action=\"./aktiv.php\" method=\"post\">\n";
	echo "\t<br><input type=\"submit\" name=\"ResetActive\" value=\"reset Active setting\">\n";
echo "</form>\n";

if( Isset($_POST["ResetActive"]) )
{
	$SQLreset = "UPDATE `User` SET `Aktiv`='0'";
	$ErgReset = db_query($SQLreset, "Reset Active");
	if ($ErgReset != 1)
		echo "Fehler beim zuruecksetzen der Activ\n";
	else
		echo "Active wurde erfolgreich zurueckgesetzt\n";
}

if( IsSet($_POST["Anzahl"]) )
	echo "<br>\n\n".Get_Text("pub_aktive_Text5_1"). $_POST["Anzahl"]. Get_Text("pub_aktive_Text5_2"). ":";

echo "<br><br>\n\n";

//ausgabe tabelle
echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
echo "<tr class=\"contenttopic\">\n";
echo "\t<td>". Get_Text("pub_aktive_Nick"). "</td>\n";
echo "\t<td>". Get_Text("pub_aktive_Anzahl"). "</td>\n";
echo "\t<td>". Get_Text("pub_aktive_Time"). "</td>\n";
echo "\t<td>". Get_Text("pub_aktive_Time"). " Weight</td>\n";
echo "\t<td>Freeloader ". Get_Text("pub_aktive_Anzahl"). "</td>\n";
echo "\t<td>Freeloader ". Get_Text("pub_aktive_Time"). "</td>\n";
echo "\t<td>". Get_Text("pub_aktive_Time"). " result</td>\n";
echo "\t<td>". Get_Text("pub_aktive_Active"). "</td>\n";
echo "</tr>\n";
	
$SQL = "
SELECT d.UID, d.nr, d.len, d.lenWeight, f.nr AS nrFree, f.len AS lenFree, d.lenWeight - COALESCE(f.len, 0) as lenReal
	FROM
		(SELECT e.UID, COUNT(s.Len) as nr, SUM(s.Len) as len, SUM( s.Len*(1+(((HOUR(s.DateS)+2)%24)<10 and ((HOUR(s.DateE)+2)%24)<10)) ) as lenWeight FROM `Shifts` AS s INNER JOIN `ShiftEntry` AS e USING(SID) WHERE NOT UID=0 GROUP BY UID) as d
		LEFT JOIN
		(SELECT UID, COUNT(Length) AS nr, SUM(Length) AS len FROM `ShiftFreeloader` GROUP BY UID) AS f
		USING(UID)
	ORDER BY lenReal DESC, nr DESC, UID";

$Erg = mysql_query($SQL, $con);
echo mysql_error($con);
$rowcount = mysql_num_rows($Erg);

$aktivecount=0;
for ($i=0; $i<$rowcount; $i++)
{
	echo "\n\n\t<tr class=\"content\">\n";
	echo "\t\t<td>". UID2Nick(mysql_result($Erg, $i, "UID")). "</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "nr"). "x</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "len"). "h</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "lenWeight"). "h</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "nrFree"). "x</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "lenFree"). "h</td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "lenReal"). "h</td>\n";

	
	echo "\t\t<td>";
	if (IsSet($_POST["Anzahl"]))
	{	
		if( $_POST["Anzahl"] < mysql_result($Erg, $i, "lenReal") )
		{
			$aktivecount++;
			if( $_POST["SendType"]=="Show..")
				echo "show set";
			else
			{
				$SQL2="UPDATE `User` SET `Aktiv`='1' WHERE `UID`='". mysql_result($Erg, $i, "UID"). "' LIMIT 1";
				$Erg2 = db_query($SQL2, "update Active State");
				if ($Erg2 != 1)
					echo "Fehler beim speichern bei Engel ".UID2Nick(mysql_result($Erg, $i, "UID"));
				else
					echo "write set";
			}
		}
	}
	echo "</td>\n";
	echo "\t</tr>\n";
} // ende Auflistung aktive Engel 

echo "</table>";

echo "<br>Anzahl eintraege: $aktivecount / $rowcount (Aktive/Mitschichten)<br><br>";

include ("../../includes/footer.php");
?>

