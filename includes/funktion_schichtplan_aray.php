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
		$TID2Name[ mysql_result($Erg, $i, "TID") ] = mysql_result($Erg, $i, "Name");
	}												

include ("./funktion_schichtplan_Tage.php");
?>
