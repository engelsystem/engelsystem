<?php
function admin_user() {
  global $user, $privileges, $tshirt_sizes, $privileges;

  $html = "";

  if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,}$/", $_REQUEST['id']) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_REQUEST['id'])) > 0) {
    $id = $_REQUEST['id'];
    if (!isset ($_REQUEST['action'])) {
      $html .= "Hallo,<br />" .
          "hier kannst du den Eintrag &auml;ndern. Unter dem Punkt 'Gekommen' " .
          "wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, " .
          "dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. " .
          "Wenn T-Shirt ein 'Ja' enth&auml;lt, bedeutet dies, dass der Engel " .
          "bereits sein T-Shirt erhalten hat.<br /><br />\n";

      $html .= "<form action=\"" . page_link_to("admin_user") . "&action=save&id=$id\" method=\"post\">\n";
      $html .= "<table border=\"0\">\n";
      $html .= "<input type=\"hidden\" name=\"Type\" value=\"Normal\">\n";

      $SQL = "SELECT * FROM `User` WHERE `UID`='" . sql_escape($id) . "'";
      $Erg = sql_query($SQL);
      list ($user_source) = sql_select($SQL);

      $html .= "<tr><td>\n";
      $html .= "<table>\n";
      $html .= "  <tr><td>Nick</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eNick\" value=\"" .
          mysql_result($Erg, 0, "Nick") . "\"></td></tr>\n";
      $html .= "  <tr><td>lastLogIn</td><td>" .
          date("Y-m-d H:i", mysql_result($Erg, 0, "lastLogIn")) . "</td></tr>\n";
      $html .= "  <tr><td>Name</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eName\" value=\"" .
          mysql_result($Erg, 0, "Name") . "\"></td></tr>\n";
      $html .= "  <tr><td>Vorname</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eVorname\" value=\"" .
          mysql_result($Erg, 0, "Vorname") . "\"></td></tr>\n";
      $html .= "  <tr><td>Alter</td><td>" .
          "<input type=\"text\" size=\"5\" name=\"eAlter\" value=\"" .
          mysql_result($Erg, 0, "Alter") . "\"></td></tr>\n";
      $html .= "  <tr><td>Telefon</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eTelefon\" value=\"" .
          mysql_result($Erg, 0, "Telefon") . "\"></td></tr>\n";
      $html .= "  <tr><td>Handy</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eHandy\" value=\"" .
          mysql_result($Erg, 0, "Handy") . "\"></td></tr>\n";
      $html .= "  <tr><td>DECT</td><td>" .
          "<input type=\"text\" size=\"4\" name=\"eDECT\" value=\"" .
          mysql_result($Erg, 0, "DECT") . "\"></td></tr>\n";
      $html .= "  <tr><td>email</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eemail\" value=\"" .
          mysql_result($Erg, 0, "email") . "\"></td></tr>\n";
      $html .= "  <tr><td>ICQ</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"eICQ\" value=\"" .
          mysql_result($Erg, 0, "ICQ") . "\"></td></tr>\n";
      $html .= "  <tr><td>jabber</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"ejabber\" value=\"" .
          mysql_result($Erg, 0, "jabber") . "\"></td></tr>\n";
      $html .= "  <tr><td>Size</td><td>" .
          html_select_key('size', 'eSize', $tshirt_sizes, mysql_result($Erg, 0, "Size")) . "</td></tr>\n";

      $options = array (
        '1' => "Yes",
        '0' => "No"
      );

      // Gekommen?
      $html .= "  <tr><td>Gekommen</td><td>\n";
      $html .= html_options('eGekommen', $options, mysql_result($Erg, 0, "Gekommen")) . "</td></tr>\n";

      // Aktiv?
      $html .= "  <tr><td>Aktiv</td><td>\n";
      $html .= html_options('eAktiv', $options, mysql_result($Erg, 0, "Aktiv")) . "</td></tr>\n";

      // T-Shirt bekommen?
      $html .= "  <tr><td>T-Shirt</td><td>\n";
      $html .= html_options('eTshirt', $options, mysql_result($Erg, 0, "Tshirt")) . "</td></tr>\n";

      $html .= "  <tr><td>Hometown</td><td>" .
          "<input type=\"text\" size=\"40\" name=\"Hometown\" value=\"" .
          mysql_result($Erg, 0, "Hometown") . "\"></td></tr>\n";

      $html .= "</table>\n</td><td valign=\"top\">" . displayavatar($id, false) . "</td></tr>";

      $html .= "</td></tr>\n";
      $html .= "</table>\n<br />\n";
      $html .= "<input type=\"submit\" value=\"Speichern\">\n";
      $html .= "</form>";

      $html .= "<hr />";

      // UserAngelType subform
      list ($user_source) = sql_select($SQL);

      $selected_angel_types = sql_select_single_col("SELECT `angeltype_id` FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user_source['UID']));
      $accepted_angel_types = sql_select_single_col("SELECT `angeltype_id` FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user_source['UID']) . " AND `confirm_user_id` IS NOT NULL");
      $nonrestricted_angel_types = sql_select_single_col("SELECT `id` FROM `AngelTypes` WHERE `restricted` = 0");

      $angel_types_source = sql_select("SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`");
      $angel_types = array();
      foreach ($angel_types_source as $angel_type)
        $angel_types[$angel_type['id']] = $angel_type['name'];

      if (isset ($_REQUEST['submit_user_angeltypes'])) {
        $selected_angel_types = isset($_REQUEST['selected_angel_types']) && is_array($_REQUEST['selected_angel_types'])?
        array_intersect($_REQUEST['selected_angel_types'], array_keys($angel_types))
        : array();
        $accepted_angel_types = isset($_REQUEST['accepted_angel_types']) && is_array($_REQUEST['accepted_angel_types'])?
        array_unique(array_diff(array_intersect($_REQUEST['accepted_angel_types'], array_keys($angel_types)), $nonrestricted_angel_types))
        : array();
        if (in_array("admin_user_angeltypes", $privileges))
          $selected_angel_types = array_merge((array) $selected_angel_types, $accepted_angel_types);
        $selected_angel_types = array_unique($selected_angel_types);

        // Assign angel-types
        sql_start_transaction();
        sql_query("DELETE FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user_source['UID']));
        $user_angel_type_info = array();
        if (!empty($selected_angel_types)) {
          $SQL = "INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`) VALUES ";
          foreach ($selected_angel_types as $selected_angel_type_id) {
            $SQL .= "(" . $user_source['UID'] . ", " . $selected_angel_type_id . "),";
            $user_angel_type_info[] = $angel_types[$selected_angel_type_id] . (in_array($selected_angel_type_id, $accepted_angel_types) ? ' (confirmed)' : '');
          }
          // remove superfluous comma
          $SQL = substr($SQL, 0, -1);
          sql_query($SQL);
        }
        if (in_array("admin_user_angeltypes", $privileges)) {
          sql_query("UPDATE `UserAngelTypes` SET `confirm_user_id` = NULL WHERE `user_id` = " . sql_escape($user_source['UID']));
          if (!empty($accepted_angel_types))
            sql_query("UPDATE `UserAngelTypes` SET `confirm_user_id` = '" . sql_escape($user['UID']) . "' WHERE `user_id` = '" . sql_escape($user_source['UID']) . "' AND `angeltype_id` IN (" . implode(',', $accepted_angel_types) . ")");
        }
        sql_stop_transaction();

        engelsystem_log("Set angeltypes of " . User_Nick_render($user_source) . " to: " . join(", ", $user_angel_type_info));
        success("Angeltypes saved.");
        redirect(page_link_to('admin_user') . '&id=' . $user_source['UID']);
      }

      $html .= form(array (
        msg(),
        form_multi_checkboxes(array('selected_angel_types' => 'gewünscht', 'accepted_angel_types' => 'akzeptiert'),
            "Angeltypes",
            $angel_types,
            array('selected_angel_types' => $selected_angel_types, 'accepted_angel_types' => array_merge($accepted_angel_types, $nonrestricted_angel_types)),
            array('accepted_angel_types' => $nonrestricted_angel_types)),
        form_submit('submit_user_angeltypes', Get_Text("Save"))
      ));

      $html .= "<hr />";

      $html .= "Hier kannst Du das Passwort dieses Engels neu setzen:<form action=\"" . page_link_to("admin_user") . "&action=change_pw&id=$id\" method=\"post\">\n";
      $html .= "<table>\n";
      $html .= "  <tr><td>Passwort</td><td>" .
          "<input type=\"password\" size=\"40\" name=\"new_pw\" value=\"\"></td></tr>\n";
      $html .= "  <tr><td>Wiederholung</td><td>" .
          "<input type=\"password\" size=\"40\" name=\"new_pw2\" value=\"\"></td></tr>\n";

      $html .= "</table>";
      $html .= "<input type=\"submit\" value=\"Speichern\">\n";
      $html .= "</form>";

      $html .= "<hr />";

      $html .= "Hier kannst Du die Benutzergruppen des Engels festlegen:<form action=\"" . page_link_to("admin_user") . "&action=save_groups&id=" . $id . "\" method=\"post\">\n";
      $html .= '<table>';

      $my_highest_group = sql_select("SELECT * FROM `UserGroups` WHERE `uid`=" . sql_escape($user['UID']) . " ORDER BY `uid` LIMIT 1");
      if (count($my_highest_group) > 0)
        $my_highest_group = $my_highest_group[0]['group_id'];

      $his_highest_group = sql_select("SELECT * FROM `UserGroups` WHERE `uid`=" . sql_escape($id) . " ORDER BY `uid` LIMIT 1");
      if (count($his_highest_group) > 0)
        $his_highest_group = $his_highest_group[0]['group_id'];

      if ($id != $user['UID'] && $my_highest_group <= $his_highest_group) {
        $groups = sql_select("SELECT * FROM `Groups` LEFT OUTER JOIN `UserGroups` ON (`UserGroups`.`group_id` = `Groups`.`UID` AND `UserGroups`.`uid` = " . sql_escape($id) . ") WHERE `Groups`.`UID` >= " . sql_escape($my_highest_group) . " ORDER BY `Groups`.`Name`");
        foreach ($groups as $group)
          $html .= '<tr><td><input type="checkbox" name="groups[]" value="' . $group['UID'] . '"' . ($group['group_id'] != "" ? ' checked="checked"' : '') . ' /></td><td>' . $group['Name'] . '</td></tr>';

        $html .= '</table>';

        $html .= "<input type=\"submit\" value=\"Speichern\">\n";
        $html .= "</form>";

        $html .= "<hr />";
      }

      $html .= "<form action=\"" . page_link_to("admin_user") . "&action=delete&id=" . $id . "\" method=\"post\">\n";
      $html .= "<input type=\"submit\" value=\"Löschen\">\n";
      $html .= "</form>";

      $html .= "<hr />";
      //$html .= funktion_db_element_list_2row("Freeloader Shifts", "SELECT `Remove_Time`, `Length`, `Comment` FROM `ShiftFreeloader` WHERE UID=" . $_REQUEST['id']);
    } else {
      switch ($_REQUEST['action']) {
        case 'save_groups' :
          if ($id != $user['UID']) {
            $my_highest_group = sql_select("SELECT * FROM `UserGroups` WHERE `uid`=" . sql_escape($user['UID']) . " ORDER BY `group_id`");
            $his_highest_group = sql_select("SELECT * FROM `UserGroups` WHERE `uid`=" . sql_escape($id) . " ORDER BY `group_id`");

            if (count($my_highest_group) > 0 && (count($his_highest_group) == 0 || ($my_highest_group[0]['group_id'] <= $his_highest_group[0]['group_id']))) {
              $groups_source = sql_select("SELECT * FROM `Groups` LEFT OUTER JOIN `UserGroups` ON (`UserGroups`.`group_id` = `Groups`.`UID` AND `UserGroups`.`uid` = " . sql_escape($id) . ") WHERE `Groups`.`UID` >= " . sql_escape($my_highest_group[0]['group_id']) . " ORDER BY `Groups`.`Name`");
              $groups = array();
              $grouplist = array ();
              foreach ($groups_source as $group) {
                $groups[$group['UID']] = $group;
                $grouplist[] = $group['UID'];
              }

              if (!is_array($_REQUEST['groups']))
                $_REQUEST['groups'] = array ();

              sql_query("DELETE FROM `UserGroups` WHERE `uid`=" . sql_escape($id));
              $user_groups_info = array();
              foreach ($_REQUEST['groups'] as $group) {
                if (in_array($group, $grouplist)) {
                  sql_query("INSERT INTO `UserGroups` SET `uid`=" . sql_escape($id) . ", `group_id`=" . sql_escape($group));
                  $user_groups_info[] = $groups[$group]['Name'];
                }
              }
              $user_source = User($id);
              engelsystem_log("Set groups of " . User_Nick_render($user_source) . " to: " . join(", ", $user_groups_info));
              $html .= success("Benutzergruppen gespeichert.", true);
            } else {
              $html .= error("Du kannst keine Engel mit mehr Rechten bearbeiten.", true);
            }
          } else {
            $html .= error("Du kannst Deine eigenen Rechte nicht bearbeiten.", true);
          }
          break;

        case 'delete' :
          if ($user['UID'] != $id) {
            $user_source = sql_select("SELECT `Nick`, `UID` FROM `User` WHERE `UID` = '" . sql_escape($id) . "' LIMIT 1");
            sql_query("DELETE FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
            sql_query("DELETE FROM `UserGroups` WHERE `uid`=" . sql_escape($id));
            sql_query("UPDATE `ShiftEntry` SET `UID`=0, `Comment`=NULL WHERE `UID`=" . sql_escape($id));
            engelsystem_log("Deleted user " . User_Nick_render($user_source));
            $html .= success("Benutzer gelöscht!", true);
          } else {
            $html .= error("Du kannst Dich nicht selber löschen!", true);
          }
          break;

        case 'save' :
          $SQL = "UPDATE `User` SET ";
          $SQL .= " `Nick` = '" . sql_escape($_POST["eNick"]) . "', `Name` = '" . sql_escape($_POST["eName"]) . "', " .
              "`Vorname` = '" . sql_escape($_POST["eVorname"]) . "', " .
              "`Telefon` = '" . sql_escape($_POST["eTelefon"]) . "', " .
              "`Handy` = '" . sql_escape($_POST["eHandy"]) . "', " .
              "`Alter` = '" . sql_escape($_POST["eAlter"]) . "', " .
              "`DECT` = '" . sql_escape($_POST["eDECT"]) . "', " .
              "`email` = '" . sql_escape($_POST["eemail"]) . "', " .
              "`ICQ` = '" . sql_escape($_POST["eICQ"]) . "', " .
              "`jabber` = '" . sql_escape($_POST["ejabber"]) . "', " .
              "`Size` = '" . sql_escape($_POST["eSize"]) . "', " .
              "`Gekommen`= '" . sql_escape($_POST["eGekommen"]) . "', " .
              "`Aktiv`= '" . sql_escape($_POST["eAktiv"]) . "', " .
              "`Tshirt` = '" . sql_escape($_POST["eTshirt"]) . "', " .
              "`Hometown` = '" . sql_escape($_POST["Hometown"]) . "' " .
              "WHERE `UID` = '" . sql_escape($id) .
              "' LIMIT 1;";
          sql_query($SQL);
          engelsystem_log("Updated user: " . $_POST["eNick"] . ", " . $_POST["eSize"] . ", arrived: " . $_POST["eGekommen"] . ", active: " . $_POST["eAktiv"] . ", tshirt: " . $_POST["eTshirt"]);
          $html .= success("Änderung wurde gespeichert...\n", true);
          break;

        case 'change_pw' :
          if ($_REQUEST['new_pw'] != "" && $_REQUEST['new_pw'] == $_REQUEST['new_pw2']) {
            set_password($id, $_REQUEST['new_pw']);
            $user_source = User($id);
            engelsystem_log("Set new password for " . User_Nick_render($user_source));
            $html .= success("Passwort neu gesetzt.", true);
          } else {
            $html .= error("Die Eingaben müssen übereinstimmen und dürfen nicht leer sein!", true);
          }
          break;
      }
    }
  } else {
    // Userliste, keine UID uebergeben...

    $html .= "<a href=\"" . page_link_to("register") . "\">Neuen Engel eintragen &raquo;</a><br /><br />\n";

    if (!isset ($_GET["OrderBy"]))
      $_GET["OrderBy"] = "Nick";
    $SQL = "SELECT * FROM `User` ORDER BY `" . sql_escape($_GET["OrderBy"]) . "` ASC";
    $angels = sql_select($SQL);

    // anzahl zeilen
    $Zeilen = count($angels);

    $html .= "Anzahl Engel: $Zeilen<br /><br />\n";

    function prepare_angel_table($angel) {
      global $privileges;
      $groups = sql_select_single_col("SELECT `Name` FROM `UserGroups` JOIN `Groups` ON (`Groups`.`UID` = `UserGroups`.`group_id`) WHERE `UserGroups`.`uid`=" . sql_escape($angel["UID"]) . " ORDER BY `Groups`.`Name`");
      $popup = '<div class="hidden">Groups: ' . implode(', ', $groups);
      if (strlen($angel["Telefon"]) > 0)
        $popup .= "<br>Tel: " . $angel["Telefon"];
      if (strlen($angel["Handy"]) > 0)
        $popup .= "<br>Handy: " . $angel["Handy"];
      if (strlen($angel["DECT"]) > 0)
        $popup .= "<br>DECT: " . $angel["DECT"];
      if (strlen($angel["Hometown"]) > 0)
        $popup .= "<br>Hometown: " . $angel["Hometown"];
      if (strlen($angel["CreateDate"]) > 0)
        $popup .= "<br>Registered: " . $angel["CreateDate"];
      if (strlen($angel["Art"]) > 0)
        $popup .= "<br>Type: " . $angel["Art"];
      if (strlen($angel["ICQ"]) > 0)
        $popup .= "<br>ICQ: " . $angel["ICQ"];
      if (strlen($angel["jabber"]) > 0)
        $popup .= "<br>Jabber: " . $angel["jabber"];
      return array(
        'Nick' => User_Nick_render($angel),
        'Name' => htmlspecialchars($angel['Vorname'] . ' ' . $angel['Name']),
        'DECT' => htmlspecialchars($angel['DECT']),
        'Alter' => htmlspecialchars($angel['Alter']),
        'email' => '<a href="mailto:' . htmlspecialchars($angel['email']) . '">' . htmlspecialchars($angel['email']) . '</a>' . $popup,
        'Gekommen' => '<img src="pic/icons/' . ($angel['Gekommen'] == 1? 'tick' : 'cross') . '.png" alt="' . $angel['Gekommen'] . '">',
        'Aktiv' => '<img src="pic/icons/' . ($angel['Aktiv'] == 1? 'tick' : 'cross') . '.png" alt="' . $angel['Aktiv'] . '">',
        'Tshirt' => '<img src="pic/icons/' . ($angel['Tshirt'] == 1? 'tick' : 'cross') . '.png" alt="' . $angel['Tshirt'] . '">',
        'Size' => $angel['Size'],
        'lastLogIn' => date('d.m.&\n\b\s\p;H:i', $angel['lastLogIn']),
        'edit' => img_button(page_link_to('admin_user') . '&id=' . $angel['UID'], 'pencil', 'edit'),
      );
    }
    $angels = array_map('prepare_angel_table', $angels);
    $Gekommen = sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Gekommen` = 1");
    $Active = sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Aktiv` = 1");
    $Tshirt = sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Tshirt` = 1");
    $angels[] = array('Nick' => '<strong>Summe</strong>', 'Gekommen' => $Gekommen, 'Aktiv' => $Active, 'Tshirt' => $Tshirt);
    $html .= table(array(
      'Nick' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=Nick">Nick</a>',
      'Name' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=Vorname">Vorname</a> <a href="' . page_link_to("admin_user") . '&amp;OrderBy=Name">Name</a>',
      'DECT' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=DECT">DECT</a>',
      'Alter' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=Alter">Alter</a>',
      'email' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=email">E-Mail</a>',
      'Gekommen' => '<div class="rotate"><a href="' . page_link_to("admin_user") . '&amp;OrderBy=Gekommen">Gekommen</a></div>',
      'Aktiv' => '<div class="rotate"><a href="' . page_link_to("admin_user") . '&amp;OrderBy=Aktiv">Aktiv</a></div>',
      'Tshirt' => '<div class="rotate"><a href="' . page_link_to("admin_user") . '&amp;OrderBy=Tshirt">T-Shirt</a></div>',
      'Size' => '<div class="rotate"><a href="' . page_link_to("admin_user") . '&amp;OrderBy=Size">Gr&ouml;&szlig;e</a></div>',
      'lastLogIn' => '<a href="' . page_link_to("admin_user") . '&amp;OrderBy=lastLogIn">Last login</a>',
      'edit' => ''),
        $angels);
  }
  return $html;
}
?>
