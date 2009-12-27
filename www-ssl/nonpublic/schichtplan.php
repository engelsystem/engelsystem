<?php
$title = "Himmel";
$header = "Schichtpl&auml;ne";
$submenus = 2;

if( isset($_GET["ausdatum"]))
	$ausdatum = $_GET["ausdatum"];
if( isset($_GET["raum"]))
{
	$raum = $_GET["raum"];
	if( $raum==-1 &&  isset($_GET["show"]))
	{
		$raum = "";
		foreach ($_GET as $k => $v) 
		{	
			if( substr($k, 0, 5) == "raum_") 
			{
				$raum = $raum. ";". $v;
			}
		}
	}
}

include ("../../includes/header.php");
include ("../../includes/funktionen.php");
include ("../../includes/funktion_schichtplan.php");
include ("../../includes/funktion_schichtplan_aray.php");
?>

<?PHP echo Get_Text("Hello").$_SESSION['Nick'].",<br>".
     	   Get_Text("pub_schicht_beschreibung"). "<br><br>";

function ShowSwitchDay()
{
	GLOBAL $VeranstaltungsTage, $VeranstaltungsTageMax, $ausdatum, $raum;
	
	echo "\n\n<table border=\"0\" width=\"100%\"><tr>\n";
	
	if( isset($VeranstaltungsTage))
	    foreach( $VeranstaltungsTage as $k => $v)
		if( $ausdatum == $v)
		{
			if( $k > 0)
				echo "\t\t\t<td align=\"left\">".
					"<a href='./schichtplan.php?ausdatum=". $VeranstaltungsTage[$k-1]. 
					"&raum=$raum'>". $VeranstaltungsTage[$k-1]. "</a></td>\n";
			if( $k < $VeranstaltungsTageMax)
				echo "\t\t\t<td align=\"right\">".
					"<a href='./schichtplan.php?ausdatum=". $VeranstaltungsTage[$k+1]. 
					"&raum=$raum'>". $VeranstaltungsTage[$k+1]. "</a></td>\n";
		}
	echo "\n\n</table>";
}

// wenn kein Datum gesetzt ist (die Seite zum ersten mal aufgerufen wird),
// das Datum auf den ersten Tag setzen...
if( !isset($ausdatum) ) 
{
	$sql = "SELECT `DateS` FROM `Shifts` WHERE `DateS` like '". gmdate("Y-m-d", time()+$gmdateOffset). "%' ORDER BY `DateS`";
//	$sql = "SELECT `DateS` FROM `Shifts` WHERE `DateS` like '2004-12-29%' ORDER BY `DateS`";
	$Erg = mysql_query($sql, $con);
	if( mysql_num_rows( $Erg ) == 0 )
	{
 		$sql = "SELECT `DateS` FROM `Shifts` ORDER BY `DateS` ASC LIMIT 0, 1";
  		$Erg = mysql_query($sql, $con);
	}
	if( mysql_num_rows( $Erg ) > 0 )
		$ausdatum = substr(mysql_result($Erg,0,"DateS"),0,10);
	else
		$ausdatum = gmdate("Y-m-d", time()+$gmdateOffset);

}



if ( !isset($raum) )
{
	// Ausgabe wenn kein Raum Ausgewählt:
	echo Get_Text("pub_schicht_auswahl_raeume"). "<br><br>\n";

	if( isset($Room))
	{
		echo "<form action=\"./schichtplan.php\" method=\"GET\">\n";
		foreach( $Room as $RoomEntry  )
		{
			echo "\t<li><input type=\"checkbox\" name=\"raum_". $RoomEntry["RID"]. "\" value=\"". $RoomEntry["RID"]." \">";
			echo "<a href='./schichtplan.php?ausdatum=$ausdatum&raum=". $RoomEntry["RID"]. "'>". $RoomEntry["Name"]. "</a>";
			echo "</input></li>\n";
		}
		echo "<input type=\"hidden\" name=\"ausdatum\" value=\"$ausdatum\">";
		echo "<input type=\"hidden\" name=\"raum\" value=\"-1\">";
		echo "<input type=\"submit\" name=\"show\" value=\"show\">\n";
		echo "</form>\n";
	}

	echo "<br><br>";
	echo Get_Text("pub_schicht_alles_1"). "<a href='./schichtplan.php?ausdatum=$ausdatum&raum=-1'> <u>".
	     Get_Text("pub_schicht_alles_2"). "</u> </a>".Get_Text("pub_schicht_alles_3");
	echo "\n<br><br>\n\n";
	echo "<hr>\n\n";
	echo Get_Text("pub_schicht_EmptyShifts"). "\n";
	
	
	// zeit die naesten freien schichten
	showEmptyShifts(); 
} 
else 
{ 	// Wenn einraum Ausgewählt ist:
	if( $raum == -1 ) 
		echo Get_Text("pub_schicht_Anzeige_1").$ausdatum.":<br><br>";
	elseif( substr( $raum, 0, 1) == ";" )
		echo Get_Text("pub_schicht_Anzeige_1").$ausdatum.":<br><br>";
	else 
		echo Get_Text("pub_schicht_Anzeige_1"). $ausdatum. 
		     Get_Text("pub_schicht_Anzeige_2"). $RoomID[$raum]. "<br><br>";

	ShowSwitchDay();

	echo "\n\n<table border=\"0\" width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "\t<tr class=\"contenttopic\">\n";
	echo "\t\t<td>start</td>\n";

	//Ausgabe Spalten überschrift
	if( $raum == -1 )
	{
		if( isset($Room))
		   foreach( $Room as $RoomEntry  )
			if (SummRoomShifts($RoomEntry["RID"]) > 0)
				echo "\t\t<th>". $RoomEntry["Name"]. "</th>\n";
	}
	elseif( substr( $raum, 0, 1) == ";" )
	{
		$words = preg_split("/;/", $raum); 
		foreach ($words as $word)
		{
			if( strlen(trim($word)) > 0)
				echo "\t\t<th>". $RoomID[trim($word)]. "</th>\n";
		}
	}
	else
		echo "\t\t<th>". $RoomID[$raum]. "</th>\n";
  	echo "\t</tr>\n";
	
	//Zeit Ausgeben
	for( $i = 0; $i < 24; $i++ )
		for( $j = 0; $j < $GlobalZeileProStunde; $j++)
		{
			$Spalten[$i * $GlobalZeileProStunde + $j] = 
				"\t<tr class=\"content\">\n\t\t";

			//Stunde:
			$SpaltenTemp="";	
			$SpaltenTemp.= ($i<10)? "0$i:": "$i:";

			//Minute
			$TempMinuten = (($j*60) / $GlobalZeileProStunde);
			$SpaltenTemp.= ($TempMinuten<10)? "0$TempMinuten": "$TempMinuten";
				
			//aktuelle stunde markieren
			if( ($j==0) && ($i == gmdate("H", time()+$gmdateOffset)) && (gmdate("Y-m-d", time()+ $gmdateOffset) == $ausdatum) )
				$SpaltenTemp = "<h1>$SpaltenTemp</h1>";
				
			$SpaltenTemp = "<td>$SpaltenTemp</td>\n";
			$Spalten[$i * $GlobalZeileProStunde + $j].= $SpaltenTemp;
		}
	
	if( $raum == -1 )
	{
		if( isset($Room))
		    foreach( $Room as $RoomEntry  )
			if (SummRoomShifts($RoomEntry["RID"]) > 0)
				CreateRoomShifts( $RoomEntry["RID"] );
	}
	elseif( substr( $raum, 0, 1) == ";" )
	{
		if( isset($Room))
		{
		    $words = preg_split("/;/", $raum); 
		    foreach ($words as $word)
		    {
			if( strlen(trim($word)) > 0)
				if (SummRoomShifts($word) > 0)
					CreateRoomShifts( $word );
		    }
		}
	}
	else
		CreateRoomShifts( $raum );
	
	//Ausageb Zeilen
	for ($i = 0; $i < (24 * $GlobalZeileProStunde); $i++) 
	{
		echo $Spalten[$i]."\t</tr>\n";
	}

  	echo "</table>\n";
	
	ShowSwitchDay();

}//if (isset($raum))

echo "<a href=\"". $_SESSION["newurl"]. "&Icon=0\">@</a>";

include ("../../includes/footer.php");
?>
