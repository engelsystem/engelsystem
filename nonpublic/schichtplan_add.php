<?php
$title = "Himmel";
$header = "Schichtpl&auml;ne";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_schichtplan.php");
include ("./inc/funktionen.php");

if (isset($newtext) && isset($SID) && isset($TID)) {
	SetHeaderGo2Back();
 
	// datum der einzutragenden schicht heraussuhen...
	$ShiftSQL = "SELECT `DateS`, `DateE` FROM `Shifts` WHERE `SID`='$SID'";
	$ShiftErg = mysql_query ($ShiftSQL, $con);
	$beginSchicht = mysql_result($ShiftErg, 0, "DateS");
	$endSchicht   = mysql_result($ShiftErg, 0, "DateE");

	// Ueberpruefung, ob der Engel bereits für eine Schicht zu dieser Zeit eingetragen ist
	$SSQL="SELECT * FROM `Shifts`".
		" INNER JOIN `ShiftEntry` ON `ShiftEntry`.`SID` = `Shifts`.`SID`".
		" WHERE ((".
			" ((`Shifts`.`DateS` >= '$beginSchicht') and ".
			"  (`Shifts`.`DateS` < '$endSchicht'))".
			" OR ".
			" ((`Shifts`.`DateE` > '$beginSchicht') and ".
			"  (`Shifts`.`DateE` <= '$endSchicht')) ".
			") and ".
			"(`ShiftEntry`.`UID` = '". $_SESSION['UID']. "'));";
	$bErg = mysql_query($SSQL, $con);

	if( mysql_num_rows($bErg) != 0 )
		echo Get_Text("pub_schichtplan_add_AllreadyinShift");
	else
	{
		//ermitteln der noch gesuchten
		$SQL3 = "SELECT * FROM `ShiftEntry`".
			" WHERE ((`SID` = '$SID') and (`TID` = '$TID') and (`UID` = '0'));";
		$Erg3 = mysql_query($SQL3, $con);

		if( mysql_num_rows($Erg3) <= 0 ) 
	 		echo Get_Text("pub_schichtplan_add_ToManyYousers");
		else
		{
			//write shift
			$SQL = "UPDATE `ShiftEntry` SET ".
				"`UID` = '". $_SESSION['UID']. "', ".
				"`Comment` = '$newtext' ".
				"WHERE ((`SID` = '$SID') and (`TID` = '$TID') and (`UID` = '0')) LIMIT 1;";
			$Erg = mysql_query($SQL, $con);

			if ($Erg != 1)
				echo Get_Text("pub_schichtplan_add_Error");
			else
				echo Get_Text("pub_schichtplan_add_WriteOK");
			
		}//TO Many USERS
	}//Allready in Shift
}
elseif (isset($SID) && isset($TID)) {
  echo Get_Text("pub_schichtplan_add_Text1"). "<br><br>\n\n".
	"<form action=\"./schichtplan_add.php\" method=\"post\">".
	"<table border=\"0\">";

  $SQL = "SELECT * FROM `Shifts` WHERE ";
  $SQL .="(SID = '".$SID."')";
  $Erg = mysql_query($SQL, $con);
  
  echo "<tr><td>". Get_Text("pub_schichtplan_add_Date"). ":</td> <td>".
       mysql_result($Erg, 0, "DateS"). "</td></tr>\n";

  echo "<tr><td>". Get_Text("pub_schichtplan_add_Place"). ":</td> <td>".
       $RoomID[ mysql_result($Erg, 0, "RID") ]. "</td></tr>\n";
       
  echo "<tr><td>". Get_Text("pub_schichtplan_add_Job"). ":</td> <td>".
       $EngelTypeID[$TID]. "</td></tr>\n";
       
  echo "<tr><td>". Get_Text("pub_schichtplan_add_Len"). ":</td> <td>".
       mysql_result($Erg, 0, "Len"). "h</td></tr>\n";
       
  echo "<tr><td>". Get_Text("pub_schichtplan_add_TextFor"). ":</td> <td>".
       mysql_result($Erg, 0, "Man"). "</td></tr>\n";
       
  echo "<tr><td valign='top'>". Get_Text("pub_schichtplan_add_Comment"). ":</td>\n <td>".
       "<textarea name='newtext' cols='50' rows='10'></textarea> </td></tr>\n";

  echo "<tr><td>&nbsp;</td>\n".
	"<td><input type=\"submit\" value=\"". Get_Text("pub_schichtplan_add_submit"). "\"> </td></tr>\n".
	"</table>\n".
	"<input type=\"hidden\" name=\"SID\" value=\"$SID\">\n".
	"<input type=\"hidden\" name=\"TID\" value=\"$TID\">\n".
	"</form>";

}

include ("./inc/footer.php");
?>
