<?php

/*#######################################################
#       Aufbau von Standart Feldern                     #
#######################################################*/

// erstellt ein Array der Reume
	$sql = "SELECT `RID`, `Name` FROM `Room` ".
		"WHERE `Show`='Y'". 
		"ORDER BY `Number`, `Name`;";
	
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++)
	{
		$Room[$i]["RID"]  = mysql_result($Erg, $i, "RID");
		$Room[$i]["Name"] = mysql_result($Erg, $i, "Name");
	
		$RoomID[ mysql_result($Erg, $i, "RID") ] =  mysql_result($Erg, $i, "Name");
	}

// erstellt ein Aray der Engeltypen
	$sql = "SELECT `TID`, `Name` FROM `EngelType` ORDER BY `Name`";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);
	for ($i=0; $i<$rowcount; $i++)
	{
		$EngelType[$i]["TID"]  = mysql_result($Erg, $i, "TID");
		$EngelType[$i]["Name"]  = mysql_result($Erg, $i, "Name").Get_Text("inc_schicht_engel");

		$EngelTypeID[ mysql_result($Erg, $i, "TID") ] = 
			mysql_result($Erg, $i, "Name").Get_Text("inc_schicht_engel");
	}												


/*#######################################################
#	gibt die engelschischten aus			#
#######################################################*/
function ausgabe_Feld_Inhalt( $SID, $Man ) 
{
// gibt, nach übergabe der der SchichtID (SID) und der RaumBeschreibung,
// die eingetragenden und und offenden Schichteintäge zurück
	global $EngelType, $EngelTypeID, $con;
	//form Config
	global $DEBUG;

	$Out = "";

	$Out.= "<table border=\"0\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" frame=\"void\">\n";

	$Out.= "\t\t\t<colgroup span=\"2\"  align=\"left\" valign=\"center\">\n".
		"\t\t\t\t<col width=\"45%\">\n".
		"\t\t\t\t<col width=\"*\">\n".
		"\t\t\t\t</colgroup>\n";

	///////////////////////////////////////////////////////////////////
	// SQL abfrage für die benötigten schichten
	///////////////////////////////////////////////////////////////////
	$SQL = "SELECT * FROM `ShiftEntry` WHERE (`SID` = '$SID') ORDER BY `TID`, `UID` DESC ;";
	$Erg = mysql_query($SQL, $con);
	
	$Anzahl = mysql_num_rows($Erg);
	$Feld=-1;
	for( $i = 0; $i < $Anzahl; $i++ )
	{
		
		$Temp_TID = mysql_result($Erg, $i, "TID");
		
		// wenn sich der Type ändert wird zumnästen feld geweckselt
		if( ($i==0) || ($Temp_TID_old != $Temp_TID) )
		{
			$Feld++;
			$Temp[$Feld]["free"]=0;
			$Temp[$Feld]["Engel"]=array();
		}
			
		$Temp[$Feld]["TID"] = $Temp_TID;
		$Temp[$Feld]["UID"] = mysql_result($Erg, $i, "UID");
		
		// ist es eine zu vergeben schicht?
		if( $Temp[$Feld]["UID"] == 0 )
			$Temp[$Feld]["free"]++;
		else
			$Temp[$Feld]["Engel"][] = $Temp[$Feld]["UID"];
		
		$Temp_TID_old = $Temp[$Feld]["TID"];
	} // FOR
	

	///////////////////////////////////////////////////////////////////
	// Aus gabe der Schicht
	///////////////////////////////////////////////////////////////////
	if( isset($Temp) && count($Temp) )
	  foreach( $Temp as $TempEntry => $TempValue )
	  {
	  	$Out.= "\t\t\t<tr>\n";
		
		// ausgabe EngelType
		$Out.= "\t\t\t\t<td>". $EngelTypeID[ $TempValue["TID"] ];
		
		// ausgabe Eingetragener Engel
		if( count($TempValue["Engel"]) > 0  )
		{
			if( count($TempValue["Engel"]) == 1  )
				$Out.= " ". trim(Get_Text("inc_schicht_ist")). ":";
			else 
				$Out.= " ". trim(Get_Text("inc_schicht_sind")). ":";
			$Out.= "</td>\n";
			$Out.= "\t\t\t\t<td>";
			
			foreach( $TempValue["Engel"] as $TempEngelEntry=> $TempEngelID )
      				$Out.= UID2Nick( $TempEngelID ). ", ";
//      				$Out.= UID2Nick( $TempEngelID ). DisplayAvatar( $TempEngelID ). ", ";
			$Out = substr( $Out, 0, strlen($Out)-2 );
		}
		else
		{
			$Out.= ":</td>\n";
			$Out.= "\t\t\t\t<td>\n";
		}
	  	
		
		// ausgabe benötigter Engel
		////////////////////////////
		if( $_SESSION['CVS']["nonpublic/schichtplan_add.php"] == "Y")
                {
			if ( $TempValue["free"] > 0)
			{
				if( count($TempValue["Engel"]) > 0)
					$Out.= ", ";
				$Out.= $TempValue["free"]. "x free ";
			}
		}
		$Out.= "</td>\n";
		$Out.= "\t\t\t</tr>\n";
	
	} // FOREACH

	$Out.= "\t\t\t</table>\n\t";
	
	return $Out;
} // function Ausgabe_Feld_Inhalt



/*#######################################################
#	gibt die engelschischten  für einen Ruam aus	#
#######################################################*/
function ausgabe_Zeile( $RID, $Time, &$AnzahlEintraege ) 
{
	global $con;
	
	$SQL = "SELECT `SID`, `Len`, `Man` FROM `Shifts` ".
		"WHERE (  (`RID` = '$RID') AND ".
			"((`DateE` like '". gmdate("Y-m-d H", $Time+3600). "%') OR ".
			" (`DateS` like '". gmdate("Y-m-d H", $Time). "%')) ) ORDER BY `DateS`;";
	
	$ErgRoom = mysql_query($SQL, $con);
	$Out= "\t<td>";
	if( mysql_num_rows( $ErgRoom)>0 )
		for( $i=1; $i<=mysql_num_rows( $ErgRoom); $i++ )
		{
			$AnzahlEintraege++;
			$Out.= ausgabe_Feld_Inhalt( mysql_result( $ErgRoom, $i-1, "SID"), 
							mysql_result( $ErgRoom, $i-1, "Man"));
			if( (mysql_num_rows( $ErgRoom) > 1) && !($i==mysql_num_rows( $ErgRoom)) )
				$Out.= "<br>";
//				$Out.= "<hr width=\"95%\" align=\"center\">\n\t\t\t\t";
			
		}
	else
		$Out.= "&nbsp;";
	
	$Out.= "</td>\n";
	
	return $Out;
}

?>
