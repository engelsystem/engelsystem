<?php
function admin_language() {
  global $user;
  global $languages;

  $html = "";
  if (!isset ($_POST["TextID"])) {
    $html .= Get_Text("Hello") . User_Nick_render($user) . ", <br />\n";
    $html .= Get_Text("pub_sprache_text1") . "<br /><br />\n";

    $html .= "<a href=\"" . page_link_to("admin_language") . "&ShowEntry=y\">" . Get_Text("pub_sprache_ShowEntry") . "</a>";
    // ausgabe Tabellenueberschift
    $html .= "\t<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n\t\t<tr>";
    $html .= "\t\t<td class=\"contenttopic\"><b>" . Get_Text("pub_sprache_TextID") . "</b></td>";
    foreach($languages as $language => $language_name) {
      $html .= "<td class=\"contenttopic\"><b>" .
          Get_Text("pub_sprache_Sprache") . " " . $language .
          "</b></td>";
      $Sprachen[$language] = $language_name;
    }
    $html .= "\t\t<td class=\"contenttopic\"><b>" . Get_Text("pub_sprache_Edit") . "</b></td>";
    $html .= "\t\t</tr>";

    if (isset ($_GET["ShowEntry"])) {
      // ausgabe eintraege
      $sprache_source = sql_select("SELECT * FROM `Sprache` ORDER BY `TextID`, `Sprache`");

      $TextID_Old = $sprache_source[0]['TextID'];
      foreach($sprache_source as $sprache_entry) {
        $TextID_New = $sprache_entry['TextID'];
        if ($TextID_Old != $TextID_New) {
          $html .= "<form action=\"" . page_link_to("admin_language") . "\" method=\"post\">";
          $html .= "<tr class=\"content\">\n";
          $html .= "\t\t<td>$TextID_Old " .
          "<input name=\"TextID\" type=\"hidden\" value=\"$TextID_Old\"> </td>\n";

          foreach ($Sprachen as $Name => $Value) {
            $Value = html_entity_decode($Value, ENT_QUOTES);
            $html .= "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Value</textarea></td>\n";
            $Sprachen[$Name] = "";
          }

          $html .= "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
          $html .= "</tr>";
          $html .= "</form>\n";
          $TextID_Old = $TextID_New;
        }
        $Sprachen[$sprache_entry['Sprache']] = $sprache_entry['Text'];
      } /*FOR*/
    }

    //fuer neu eintraege
    $html .= "<form action=\"" . page_link_to("admin_language") . "\" method=\"post\">";
    $html .= "<tr class=\"content\">\n";
    $html .= "\t\t<td><input name=\"TextID\" type=\"text\" size=\"40\" value=\"new\"> </td>\n";

    foreach ($Sprachen as $Name => $Value)
      $html .= "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Name Text</textarea></td>\n";

    $html .= "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
    $html .= "</tr>";
    $html .= "</form>\n";

    $html .= "</table>\n";
  } /*if( !isset( $TextID )  )*/
  else {
    $html .= "edit: " . $_POST["TextID"] . "<br /><br />";
    foreach ($_POST as $k => $v) {
      if ($k != "TextID") {
        $sql_test = "SELECT * FROM `Sprache` " .
            "WHERE `TextID`='" . sql_escape($_POST["TextID"])
            . "' AND `Sprache`='"
            . sql_escape($k) . "'";

        $erg_test = sql_select("SELECT * FROM `Sprache` WHERE `TextID`='" . sql_escape($_POST["TextID"]) . "' AND `Sprache`='" . sql_escape($k) . "'");
        if (count($erg_test) == 0) {
          $sql_save = "INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) " .
              "VALUES ('" . sql_escape($_POST["TextID"]) . "', '"
              . sql_escape($k) . "', '"
              . sql_escape($v) . "')";

          $html .= $sql_save . "<br />";
          $Erg = sql_query($sql_save);
          $html .= success("$k Save: OK<br />\n", true);
        } else
          if ($erg_test[0]['Text'] != $v) {
          $sql_save = "UPDATE `Sprache` SET `Text`='"
          . sql_escape($v) . "' " .
          "WHERE `TextID`='"
          . sql_escape($_POST["TextID"])
          . "' AND `Sprache`='" . sql_escape($k) . "' ";

          $html .= $sql_save . "<br />";
          $Erg = sql_query($sql_save);
          $html .= success(" $k Update: OK<br />\n", true);
        } else
          $html .= "\t $k no changes<br />\n";
      }
    }

  }
  return $html;
}
?>

