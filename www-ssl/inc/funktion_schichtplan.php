<?php


/*#######################################################
#	gibt die engelschischten aus			#
#######################################################*/
function ausgabe_Feld_Inhalt( $SID, $Man ) 
{
// gibt, nach übergabe der der SchichtID (SID) und der RaumBeschreibung,
// die eingetragenden und und offenden Schichteintäge zurück
	global $EngelType, $EngelTypeID, $con, $DEBUG;

	$Spalten = "";
	
	///////////////////////////////////////////////////////////////////
	// Schow Admin Page
	///////////////////////////////////////////////////////////////////
	if( $_SESSION['CVS'][ "admin/schichtplan.php" ] == "Y" )
	{
		$Spalten.= "<a href=\"./../admin/schichtplan.php?action=change&SID=$SID\">edit</a><br>\n\t\t";
	}
	

	///////////////////////////////////////////////////////////////////
  	// Ausgabe des Schischtnamens
	///////////////////////////////////////////////////////////////////
	$SQL = "SELECT `URL` FROM `Shifts` WHERE (`SID` = '$SID');";
	$Erg = mysql_query($SQL, $con);
	if( mysql_result($Erg, 0, 0) != "")
		$Spalten.="<a href=\"". mysql_result($Erg, 0, 0). "\" target=\"_black\"><u>$Man:</u></a><br>";
	else
  		$Spalten.="<u>".$Man.":</u><br>";


	///////////////////////////////////////////////////////////////////
	// SQL abfrage für die benötigten schichten
	///////////////////////////////////////////////////////////////////
	$SQL = "SELECT * FROM `ShiftEntry` WHERE (`SID` = '$SID') ORDER BY `TID`, `UID` DESC ;";
	$Erg = mysql_query($SQL, $con);
	
	$Anzahl = mysql_num_rows($Erg);
	$Feld=0;
	$Temp_TID_old=-1;
	for( $i = 0; $i < $Anzahl; $i++ )
	{
		if( isset($Temp[$Feld]["TID"]))
			$Temp_TID_old = $Temp[$Feld]["TID"];
		if( isset($Temp[$Feld]["UID"]))
			$Temp_UID_old = $Temp[$Feld]["UID"];
		
		$Temp_TID = mysql_result($Erg, $i, "TID");
		
		// wenn sich der Type ändert wird zumnästen feld geweckselt
		if( $Temp_TID_old != $Temp_TID )
			$Feld++;
			
		$Temp[$Feld]["TID"] = $Temp_TID;
		$Temp[$Feld]["UID"] = mysql_result($Erg, $i, "UID");
		
		// sonderfall ersten durchlauf
		if( $i == 0 )
		{
			$Temp_TID_old = $Temp[$Feld]["TID"];
			$Temp_UID_old = $Temp[$Feld]["UID"];
		}
		
		// ist es eine zu vergeben schicht?
		if( $Temp[$Feld]["UID"] == 0 )
		{
			if( isset($Temp[$Feld]["free"]))
				$Temp[$Feld]["free"]++;
			else
				$Temp[$Feld]["free"]=1;
		}
		else
			$Temp[$Feld]["Engel"][] = $Temp[$Feld]["UID"];
	} // FOR
	

	///////////////////////////////////////////////////////////////////
	// Aus gabe der Schicht
	///////////////////////////////////////////////////////////////////
	if( isset($Temp))
	  if( count($Temp) )
	    foreach( $Temp as $TempEntry => $TempValue )
	    {
		if( !isset($TempValue["free"]))
			$TempValue["free"] = 0;
		
		// ausgabe EngelType
		$Spalten.= $EngelTypeID[ $TempValue["TID"] ]. " ";
		
		// ausgabe Eingetragener Engel
		if( isset($TempValue["Engel"]))
		  if( count($TempValue["Engel"]) > 0  )
		  {
			if( count($TempValue["Engel"]) == 1  )
				$Spalten.= Get_Text("inc_schicht_ist"). ":<br>\n\t\t";
			else 
				$Spalten.= Get_Text("inc_schicht_sind"). ":<br>\n\t\t";
			
			foreach( $TempValue["Engel"] as $TempEngelEntry=> $TempEngelID )
			{

				if( $_SESSION['CVS'][ "admin/schichtplan.php" ] == "Y" )
				{
					if( UIDgekommen( $TempEngelID ) == "1")
	      					$Spalten.= "&nbsp;&nbsp;<span style=\"color: blue;\">". 
							   UID2Nick( $TempEngelID ).
      							   DisplayAvatar( $TempEngelID ).
							   "</span><br>\n\t\t";
					else
      						$Spalten.= "&nbsp;&nbsp;<span style=\"color: red;\">". 
							   UID2Nick( $TempEngelID ).
      							   DisplayAvatar( $TempEngelID ).
							   "</span><br>\n\t\t";
				}
				else
      					$Spalten.= "&nbsp;&nbsp;". UID2Nick( $TempEngelID ).
      						   DisplayAvatar( $TempEngelID ).
						   "<br>\n\t\t";
			}
			$Spalten = substr( $Spalten, 0, strlen($Spalten)-7 );
		  }
		
		// ausgabe benötigter Engel
		////////////////////////////
		//in vergangenheit
		$SQLtime = "SELECT `DateE` FROM `Shifts` WHERE (SID='$SID' AND `DateE` >= '". 
			gmdate("Y-m-d H:i:s", time()+ 3600). "')";
		$Ergtime = mysql_query($SQLtime, $con);
		if( mysql_num_rows( $Ergtime) > 0)
		{
		 //mit sonder status
		 $SQLerlaubnis = "SELECT Name FROM `EngelType` WHERE TID = '". $TempValue["TID"]. "'";
		 $Ergerlaubnis =  mysql_query( $SQLerlaubnis, $con);
		 if( mysql_num_rows( $Ergerlaubnis))
		   //setzen wenn nicht definiert
		   if( !isset($_SESSION['CVS'][mysql_result( $Ergerlaubnis, 0, "Name")]))
		   	 $_SESSION['CVS'][mysql_result( $Ergerlaubnis, 0, "Name")] = "Y";
		   if( $_SESSION['CVS'][mysql_result( $Ergerlaubnis, 0, "Name")] == "Y")
		     if( $TempValue["free"] > 0 )
		     {
			$Spalten.= "<br>\n\t\t&nbsp;&nbsp;<a href=\"./schichtplan_add.php?SID=$SID&TID=".
				   $TempValue["TID"]."\">";
			$Spalten.= $TempValue["free"];
			if( $TempValue["free"] != 1 )
				$Spalten.= Get_Text("inc_schicht_weitere").
    					   " ".Get_Text("inc_schicht_Engel").
    					   Get_Text("inc_schicht_wird");
			else
				$Spalten.= Get_Text("inc_schicht_weiterer").
    					   " ".Get_Text("inc_schicht_Engel").
					   Get_Text("inc_schicht_werden");
			$Spalten.= Get_Text("inc_schicht_noch_gesucht");
			$Spalten.= "</a>";
		     }   
		}
		else
		{
			if( isset($TempValue["free"]))
				if( $TempValue["free"] > 0 )
					$Spalten.= "<br>\n\t\t&nbsp;&nbsp;<h3><a>Fehlen noch: ". 
							$TempValue["free"]. "</a></h3>";
		}
		$Spalten.= "<br>\n\t\t";
	
	} // FOREACH
	return $Spalten;
} // function Ausgabe_Feld_Inhalt



/*#######################################################
#	gibt die engelschischten Druckergerecht aus	#
#######################################################*/
function Ausgabe_Feld_Inhalt_Druck($RID, $Man ) 
{
// gibt, nach übergabe der der SchichtID (SID) und der RaumBeschreibung,
// die eingetragenden und und offenden Schichteintäge zurück


} // function Ausgabe_Feld_Inhalt




/*#######################################################
#	Ausgabe der Raum Spalten			#
#######################################################*/
function CreateRoomShifts( $raum )
{
	global $Spalten, $ausdatum, $con, $DEBUG, $GlobalZeileProStunde;
	
	/////////////////////////////////////////////////////////////
	// beginnt die erste schicht vor dem heutigen tag und geht darüber hinaus
	/////////////////////////////////////////////////////////////
	$SQLSonder = "SELECT `SID`, `DateS`, `DateE` , `Len`, `Man` FROM `Shifts` ".
		     "WHERE ((`RID` = '$raum') AND (`DateE` > '$ausdatum 23:59:59') AND ".
		     	"(`DateS` < '$ausdatum 00:00:00') ) ORDER BY `DateS`;";
	$ErgSonder = mysql_query($SQLSonder, $con);
	if( (mysql_num_rows( $ErgSonder) > 1) )
	{
		echo Get_Text("pub_schichtplan_colision"). " ".
			mysql_result($ErgSonder, $i, "DateS"). 
			" '". mysql_result($ErgSonder, $i, "Man"). "' ".
			" (".  mysql_result($ErgSonder, $i, "SID"). " R$raum) (00-24)<br><br>";
	}
	elseif( (mysql_num_rows( $ErgSonder) == 1) )
	{
		$Spalten[0].=	"\t\t<td valign=\"top\" rowspan=\"". (24 * $GlobalZeileProStunde). "\">\n".
				"\t\t\t<h3>&uarr;&uarr;&uarr;</h3>".
	        		Ausgabe_Feld_Inhalt( mysql_result($ErgSonder, 0, "SID"), 
						     mysql_result($ErgSonder, 0, "Man") ).
				"\t\t\t<h3>&darr;&darr;&darr;</h3>".
	       			"\n\t\t</td>\n";
		return;
	}
	
	$ZeitZeiger = 0;

	/////////////////////////////////////////////////////////////
	// beginnt die erste schicht vor dem heutigen tag?
	/////////////////////////////////////////////////////////////
	$SQLSonder = "SELECT `SID`, `DateS`, `DateE` , `Len`, `Man` FROM `Shifts` ".
		     "WHERE ((`RID` = '$raum') AND (`DateE` > '$ausdatum 00:00:00') AND ".
		     	"(`DateS` < '$ausdatum 00:00:00') ) ORDER BY `DateS`;";
	$ErgSonder = mysql_query($SQLSonder, $con);
	if( (mysql_num_rows( $ErgSonder) > 1) )
	{
		echo Get_Text("pub_schichtplan_colision"). " ".
			mysql_result($ErgSonder, $i, "DateS"). 
			" '". mysql_result($ErgSonder, $i, "Man"). "' ".
			" (".  mysql_result($ErgSonder, $i, "SID"). " R$raum) (00-xx)<br><br>";
	}
	elseif( (mysql_num_rows( $ErgSonder) == 1) )
	{
		$ZeitZeiger =	substr( mysql_result($ErgSonder, 0, "DateE"), 11, 2 )+
				(substr( mysql_result($ErgSonder, 0, "DateE"), 14, 2 ) / 60);
		$Spalten[0].=	"\t\t<td valign=\"top\" rowspan=\"". ($ZeitZeiger * $GlobalZeileProStunde). "\">\n".
				"\t\t\t<h3>&uarr;&uarr;&uarr;</h3>".
	        		Ausgabe_Feld_Inhalt( mysql_result($ErgSonder, 0, "SID"), 
						     mysql_result($ErgSonder, 0, "Man") ).
	       			"\n\t\t</td>\n";
	}
		     
	/////////////////////////////////////////////////////////////
	// gibt die schichten für den tag aus
	/////////////////////////////////////////////////////////////
	$SQL =	"SELECT `SID`, `DateS`, `Len`, `Man` FROM `Shifts` ".
		"WHERE ((`RID` = '$raum') and ".
		"(`DateS` >= '$ausdatum $ZeitZeiger:00:00') and ".
		"(`DateS` like '$ausdatum%')) ORDER BY `DateS`;";
      	$Erg = mysql_query($SQL, $con);
	for( $i = 0; $i < mysql_num_rows($Erg); ++$i )
	{	
		$ZeitPos = substr( mysql_result($Erg, $i, "DateS"), 11, 2 )+
			  (substr( mysql_result($Erg, $i, "DateS"), 14, 2 ) / 60);
		$len = mysql_result($Erg, $i, "Len");
		
		if( $ZeitZeiger < $ZeitPos  )
		{
//			for( $iLeer = ($ZeitZeiger*$GlobalZeileProStunde); $iLeer<($ZeitPos*$GlobalZeileProStunde); $iLeer++)
//				$Spalten[$iLeer] .= "\t\t<td valign=\"top\" rowspan=\"1\"></td>\n";
												
	       		$Spalten[$ZeitZeiger * $GlobalZeileProStunde].=	
				"\t\t<td valign=\"top\" rowspan=\"". ( ($ZeitPos - $ZeitZeiger ) * $GlobalZeileProStunde ). "\">&nbsp;</td>\n";

			$ZeitZeiger += $ZeitPos - $ZeitZeiger;
		}
		if($ZeitZeiger == $ZeitPos )
		{
			//sonderfall wenn die schicht über dei 24 stunden hinaus geht
			// (eintrag abkürzen, pfeiel ausgeben)
	       		$Spalten[$ZeitZeiger * $GlobalZeileProStunde].= 
					"\t\t<td valign=\"top\" rowspan=\"". 
					( ( ($len+$ZeitZeiger)? $len : 24-$len+$ZeitZeiger) * $GlobalZeileProStunde). 
					"\">\n".
					"\t\t\t".
	        			Ausgabe_Feld_Inhalt( mysql_result($Erg, $i, "SID"), 
							     mysql_result($Erg, $i, "Man") ).
					(( ($ZeitZeiger+$len) > 24)? "\t\t\t<h3>&darr;&darr;&darr;</h3>" : "").
	       				"\n\t\t</td>\n";
			$ZeitZeiger += $len;
		}
		else
		{
			echo Get_Text("pub_schichtplan_colision"). " ".
				mysql_result($Erg, $i, "DateS"). 
				" '". mysql_result($Erg, $i, "Man"). "' ".
				" (".  mysql_result($Erg, $i, "SID"). " R$raum) (xx-xx)<br><br>";
		}
	}
	if( $ZeitZeiger < 24 )
       		$Spalten[($ZeitZeiger * $GlobalZeileProStunde)].=	
					"\t\t<td valign=\"top\" rowspan=\"". 
					((24 - $ZeitZeiger) * $GlobalZeileProStunde ). 
					"\">&nbsp;</td>\n";
} // function CreateRoomShifts


/*#######################################################
#	Ausgabe der freien schichten			#
#######################################################*/
function showEmptyShifts( )
{
	global $con, $DEBUG, $RoomID;

	echo "<table border=\"1\">\n";
	echo "<tr>\n";
	echo "\t<th>". Get_Text("inc_schicht_date"). "</th>\n";
	echo "\t<th>". Get_Text("inc_schicht_time"). "</th>\n";
	echo "\t<th>". Get_Text("inc_schicht_room"). "</th>\n";
	echo "\t<th>". Get_Text("inc_schicht_commend"). "</th>\n";
	echo "</tr>\n";
	
	$sql = "SELECT `SID`, `DateS`, `Man`, `RID` FROM `Shifts` ".
		"WHERE (`Shifts`.`DateS`>='". gmdate("Y-m-d H:i:s", time()+3600). "') ".
		"ORDER BY `DateS`, `RID`;";
	$Erg = mysql_query($sql, $con);

	$angezeigt = 0;
	for ($i=0; ($i<mysql_num_rows($Erg)) && ($angezeigt< 15); $i++)
	  if( isset($RoomID[mysql_result( $Erg, $i, "RID")]))
	   if( $RoomID[mysql_result( $Erg, $i, "RID")]!="" )
	   {
 		$Sql2 = "SELECT `UID` FROM `ShiftEntry` ".
			"WHERE `SID`=". mysql_result( $Erg, $i, "SID"). " AND ".
				"`UID`='0';";
		$Erg2 = mysql_query($Sql2, $con);
		
		if( mysql_num_rows($Erg2)>0)
		{
			$angezeigt++;
			echo "<tr>\n";
			echo "\t<td>". substr(mysql_result( $Erg, $i, "DateS"), 0, 10). "</td>\n";
			echo "\t<td>". substr(mysql_result( $Erg, $i, "DateS"), 11). "</td>\n";
			echo "\t<td>". $RoomID[mysql_result( $Erg, $i, "RID")]. "</td>\n";
			echo "\t<td>". 
				ausgabe_Feld_Inhalt( mysql_result( $Erg, $i, "SID"), mysql_result( $Erg, $i, "Man")).
				"</td>\n";
			echo "</tr>\n";
		}
	   }
	
	echo "</table>\n";
	
} //function showEmptyShifts

	
/*#######################################################
#	Gibt die anzahl der Schichten im Raum zurück	#
#######################################################*/
function SummRoomShifts( $raum )
{
	global $ausdatum, $con, $DEBUG, $GlobalZeileProStunde;
	
	$SQLSonder = "SELECT `SID`, `DateS`, `Len`, `Man` FROM `Shifts` ".
		     "WHERE ((`RID` = '$raum') AND (`DateE` >= '$ausdatum 00:00:00') AND ".
		     	"(`DateS` <= '$ausdatum 23:59:59') ) ORDER BY `DateS`;";

      	$ErgSonder = mysql_query($SQLSonder, $con);
	
	return mysql_num_rows($ErgSonder);
}

?>
