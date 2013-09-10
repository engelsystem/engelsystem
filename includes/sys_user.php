<?php

function User_Nick_render($user_source) {
  global $user, $privileges;
  if($user['UID'] == $user_source['UID'] || in_array('user_shifts_admin', $privileges))
    return '<a href="' . page_link_to('user_myshifts') . '&amp;id=' . $user_source['UID'] . '">' . htmlspecialchars($user_source['Nick']) . '</a>';
  else
    return htmlspecialchars($user_source['Nick']);
}


/**
 * Available T-Shirt sizes
 */
$tshirt_sizes = array (
  '' => "Please select...",
  'S' => "S",
  'M' => "M",
  'L' => "L",
  'XL' => "XL",
  '2XL' => "2XL",
  '3XL' => "3XL",
  '4XL' => "4XL",
  '5XL' => "5XL",
  'S-G' => "S Girl",
  'M-G' => "M Girl",
  'L-G' => "L Girl",
  'XL-G' => "XL Girl"
);

function UID2Nick($UID) {
  if ($UID > 0)
    $SQL = "SELECT Nick FROM `User` WHERE UID='" . sql_escape($UID) . "'";
  else
    $SQL = "SELECT Name FROM `Groups` WHERE UID='" . sql_escape($UID) . "'";

  $Erg = sql_select($SQL);

  if (count($Erg) > 0) {
    if ($UID > 0)
      return $Erg[0]['Nick'];
    else
      return "Group-" . $Erg[0]['Name'];
  } else {
    if ($UID == -1)
      return "Guest";
    else
      return "UserID $UID not found";
  }
}

function TID2Type($TID) {
  global $con;

  $SQL = "SELECT Name FROM `EngelType` WHERE TID='" . sql_escape($TID) . "'";
  $Erg = mysql_query($SQL, $con);

  if (mysql_num_rows($Erg))
    return mysql_result($Erg, 0);
  else
    return "";
}

function ReplaceSmilies($neueckig) {
  $neueckig = str_replace(";o))", "<img src=\"pic/smiles/icon_redface.gif\">", $neueckig);
  $neueckig = str_replace(":-))", "<img src=\"pic/smiles/icon_redface.gif\">", $neueckig);
  $neueckig = str_replace(";o)", "<img src=\"pic/smiles/icon_wind.gif\">", $neueckig);
  $neueckig = str_replace(":)", "<img src=\"pic/smiles/icon_smile.gif\">", $neueckig);
  $neueckig = str_replace(":-)", "<img src=\"pic/smiles/icon_smile.gif\">", $neueckig);
  $neueckig = str_replace(":(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
  $neueckig = str_replace(":-(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
  $neueckig = str_replace(":o(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
  $neueckig = str_replace(":o)", "<img src=\"pic/smiles/icon_lol.gif\">", $neueckig);
  $neueckig = str_replace(";o(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
  $neueckig = str_replace(";(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
  $neueckig = str_replace(";-(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
  $neueckig = str_replace("8)", "<img src=\"pic/smiles/icon_rolleyes.gif\">", $neueckig);
  $neueckig = str_replace("8o)", "<img src=\"pic/smiles/icon_rolleyes.gif\">", $neueckig);
  $neueckig = str_replace(":P", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
  $neueckig = str_replace(":-P", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
  $neueckig = str_replace(":oP", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
  $neueckig = str_replace(";P", "<img src=\"pic/smiles/icon_mad.gif\">", $neueckig);
  $neueckig = str_replace(";oP", "<img src=\"pic/smiles/icon_mad.gif\">", $neueckig);
  $neueckig = str_replace("?)", "<img src=\"pic/smiles/icon_question.gif\">", $neueckig);

  return $neueckig;
}

function GetPictureShow($UID) {
  global $con;

  $SQL = "SELECT `show` FROM `UserPicture` WHERE `UID`='" . sql_escape($UID) . "'";
  $res = mysql_query($SQL, $con);

  if (mysql_num_rows($res) == 1)
    return mysql_result($res, 0, 0);
  else
    return "";
}

function displayPicture($UID, $height = "30") {
  global $url, $ENGEL_ROOT;

  if ($height > 0)
    return ("<div class=\"avatar\"><img src=\"" . $url . $ENGEL_ROOT . "ShowUserPicture.php?UID=$UID\" height=\"$height\" alt=\"picture of USER$UID\" class=\"photo\"></div>");
  else
    return ("<div class=\"avatar\"><img class=\"avatar\" src=\"" . $url . $ENGEL_ROOT . "ShowUserPicture.php?UID=$UID\" alt=\"picture of USER$UID\"></div>");
}

function displayavatar($UID, $height = "30") {
  global $con, $url, $ENGEL_ROOT;

  if (GetPictureShow($UID) == 'Y')
    return "&nbsp;" . displayPicture($UID, $height);

  $user = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($UID) . " LIMIT 1");
  if (count($user) > 0)
    if ($user[0]['Avatar'] > 0)
    return '<div class="avatar">' . ("&nbsp;<img src=\"pic/avatar/avatar" . $user[0]['Avatar'] . ".gif\">") . '</div>';
}

function UIDgekommen($UID) {
  global $con;

  $SQL = "SELECT `Gekommen` FROM `User` WHERE UID='" . sql_escape($UID) . "'";
  $Erg = mysql_query($SQL, $con);

  if (mysql_num_rows($Erg))
    return mysql_result($Erg, 0);
  else
    return "0";
}
?>
