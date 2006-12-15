<?php

function UID2Nick($UID) 
{
  global $con;
  
  $SQL = "SELECT Nick FROM `User` WHERE UID='$UID'";
  $Erg = mysql_query($SQL, $con);

  //echo $UID."#";
  if( mysql_num_rows($Erg))
	return mysql_result($Erg, 0);
  else
  {
  	if( $UID == -1)
		return "logout User";
	else
	  	return "UserID $UID not found";
  }
}


function TID2Type($TID) 
{
  global $con;
  
  $SQL = "SELECT Name FROM `EngelType` WHERE TID='$TID'";
  $Erg = mysql_query($SQL, $con);

  return mysql_result($Erg, 0);
}


function ReplaceSmilies($eckig) {

	$neueckig = $eckig;
	$neueckig = str_replace(";o))","<img src=\"./inc/smiles/icon_redface.gif\">",$neueckig);
	$neueckig = str_replace(":-))","<img src=\"./inc/smiles/icon_redface.gif\">",$neueckig);
	$neueckig = str_replace(";o)","<img src=\"./inc/smiles/icon_wind.gif\">",$neueckig);
	$neueckig = str_replace(":)","<img src=\"./inc/smiles/icon_smile.gif\">",$neueckig);
        $neueckig = str_replace(":-)","<img src=\"./inc/smiles/icon_smile.gif\">",$neueckig);
	$neueckig = str_replace(":(","<img src=\"./inc/smiles/icon_sad.gif\">",$neueckig);
        $neueckig = str_replace(":-(","<img src=\"./inc/smiles/icon_sad.gif\">",$neueckig);
	$neueckig = str_replace(":o(","<img src=\"./inc/smiles/icon_sad.gif\">",$neueckig);
	$neueckig = str_replace(":o)","<img src=\"./inc/smiles/icon_lol.gif\">",$neueckig);
	$neueckig = str_replace(";o(","<img src=\"./inc/smiles/icon_cry.gif\">",$neueckig);
	$neueckig = str_replace(";(","<img src=\"./inc/smiles/icon_cry.gif\">",$neueckig);
        $neueckig = str_replace(";-(","<img src=\"./inc/smiles/icon_cry.gif\">",$neueckig);
        $neueckig = str_replace("8)","<img src=\"./inc/smiles/icon_rolleyes.gif\">",$neueckig);
	$neueckig = str_replace("8o)","<img src=\"./inc/smiles/icon_rolleyes.gif\">",$neueckig);
	$neueckig = str_replace(":P","<img src=\"./inc/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(":-P","<img src=\"./inc/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(":oP","<img src=\"./inc/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(";P","<img src=\"./inc/smiles/icon_mad.gif\">",$neueckig);
	$neueckig = str_replace(";oP","<img src=\"./inc/smiles/icon_mad.gif\">",$neueckig);
	$neueckig = str_replace("?)","<img src=\"./inc/smiles/icon_question.gif\">",$neueckig);
	return $neueckig;
}


/* Parameter: 
	<UserID>
	[<Höhe des Bildes (wenn die höhe kleiner 1 ist wird die höhe nicht begrenzt)>] */
function displayavatar($UID, $height="30") 
{
	global $con;

	// ist ein foto hinterlegt?
	$SQL= "SELECT * FROM `UserPicture` WHERE `UID`='$UID' AND `show`='Y'";
	$res = mysql_query( $SQL, $con);
	if( mysql_num_rows($res) == 1)
		if( $height > 0)
			return( "&nbsp;<img src=\"./inc/ShowUserPicture.php?UID=$UID\" height=\"$height\" alt=\"picture of USER$UID\">");
		else
			return( "&nbsp;<img src=\"./inc/ShowUserPicture.php?UID=$UID\" alt=\"picture of USER$UID\">");
        
	// show avator
	$asql = "select * from User where UID = $UID";
	$aerg = mysql_query ($asql, $con);
	if( mysql_num_rows($aerg) )
		if( mysql_result($aerg, 0, "Avatar") > 0)
          		return ("&nbsp;<img src=\"./inc/avatar/avatar". mysql_result($aerg, 0, "Avatar"). ".gif\">");

}

function UIDgekommen($UID) 
{
  global $con;
  
  $SQL = "SELECT `Gekommen` FROM `User` WHERE UID='$UID'";
  $Erg = mysql_query($SQL, $con);

  //echo $UID."#";
  if( mysql_num_rows($Erg))
	return mysql_result($Erg, 0);
  else
  	return "0";
}

?>
