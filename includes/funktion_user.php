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

  if( mysql_num_rows($Erg))
  	return mysql_result($Erg, 0);
  else
  	return "";
}


function ReplaceSmilies($eckig) {

	$neueckig = $eckig;
	$neueckig = str_replace(";o))","<img src=\"/pic/smiles/icon_redface.gif\">",$neueckig);
	$neueckig = str_replace(":-))","<img src=\"/pic/smiles/icon_redface.gif\">",$neueckig);
	$neueckig = str_replace(";o)","<img src=\"/pic/smiles/icon_wind.gif\">",$neueckig);
	$neueckig = str_replace(":)","<img src=\"/pic/smiles/icon_smile.gif\">",$neueckig);
        $neueckig = str_replace(":-)","<img src=\"/pic/smiles/icon_smile.gif\">",$neueckig);
	$neueckig = str_replace(":(","<img src=\"/pic/smiles/icon_sad.gif\">",$neueckig);
        $neueckig = str_replace(":-(","<img src=\"/pic/smiles/icon_sad.gif\">",$neueckig);
	$neueckig = str_replace(":o(","<img src=\"/pic/smiles/icon_sad.gif\">",$neueckig);
	$neueckig = str_replace(":o)","<img src=\"/pic/smiles/icon_lol.gif\">",$neueckig);
	$neueckig = str_replace(";o(","<img src=\"/pic/smiles/icon_cry.gif\">",$neueckig);
	$neueckig = str_replace(";(","<img src=\"/pic/smiles/icon_cry.gif\">",$neueckig);
        $neueckig = str_replace(";-(","<img src=\"/pic/smiles/icon_cry.gif\">",$neueckig);
        $neueckig = str_replace("8)","<img src=\"/pic/smiles/icon_rolleyes.gif\">",$neueckig);
	$neueckig = str_replace("8o)","<img src=\"/pic/smiles/icon_rolleyes.gif\">",$neueckig);
	$neueckig = str_replace(":P","<img src=\"/pic/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(":-P","<img src=\"/pic/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(":oP","<img src=\"/pic/smiles/icon_evil.gif\">",$neueckig);
	$neueckig = str_replace(";P","<img src=\"/pic/smiles/icon_mad.gif\">",$neueckig);
	$neueckig = str_replace(";oP","<img src=\"/pic/smiles/icon_mad.gif\">",$neueckig);
	$neueckig = str_replace("?)","<img src=\"/pic/smiles/icon_question.gif\">",$neueckig);
	return $neueckig;
}


function GetPicturShow($UID)
{
	global $con;

	$SQL= "SELECT `show` FROM `UserPicture` WHERE `UID`='$UID'";
	$res = mysql_query( $SQL, $con);
	if( mysql_num_rows($res) == 1)
		return mysql_result( $res, 0, 0);
	else
		return "";
}


/* Parameter: 
	<UserID>
	[<Höhe des Bildes (wenn die höhe kleiner 1 ist wird die höhe nicht begrenzt)>] */
function displayPictur($UID, $height="30") 
{
	if( $height > 0)
		return( "<img src=\"/ShowUserPicture.php?UID=$UID\" height=\"$height\" alt=\"picture of USER$UID\" class=\"photo\">");
	else
		return( "<img src=\"/ShowUserPicture.php?UID=$UID\" alt=\"picture of USER$UID\">");
}


/* Parameter: 
	<UserID>
	[<Höhe des Bildes (wenn die höhe kleiner 1 ist wird die höhe nicht begrenzt)>] */
function displayavatar( $UID, $height="30") 
{
	global $con;

	if( GetPicturShow($UID) == 'Y')
		return "&nbsp;". displayPictur(  $UID, $height);
        
	// show avator
	$asql = "select * from User where UID = $UID";
	$aerg = mysql_query ($asql, $con);
	if( mysql_num_rows($aerg) )
		if( mysql_result($aerg, 0, "Avatar") > 0)
          		return ("&nbsp;<img src=\"/pic/avatar/avatar". mysql_result($aerg, 0, "Avatar"). ".gif\">");

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
