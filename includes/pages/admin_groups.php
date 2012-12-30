<?php
function admin_groups() {
  global $user;

  $html = "";
  $groups = sql_select("SELECT * FROM `Groups` ORDER BY `Name`");
  if (!isset ($_REQUEST["action"])) {
    $groups_html = "";
    foreach ($groups as $group) {
      $groups_html .= sprintf(
          '<tr><td>%s</td>',
          $group['Name']
      );
      $privileges = sql_select("SELECT * FROM `GroupPrivileges` JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `group_id`=" . sql_escape($group['UID']));
      $privileges_html = array ();

      foreach ($privileges as $priv)
        $privileges_html[] = $priv['name'];

      $groups_html .= sprintf(
          '<td>%s</td>'
          . '<td><a href="%s&action=edit&id=%s">Ändern</a></td>',
          join(', ', $privileges_html),
          page_link_to("admin_groups"),
          $group['UID']
      );
    }

    return template_render('../templates/admin_groups.html', array (
      'nick' => User_Nick_render($user),
      'groups' => $groups_html
    ));
  } else {
    switch ($_REQUEST["action"]) {
      case 'edit' :
        if (isset ($_REQUEST['id']) && preg_match("/^-[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Groups ID.", true);

        $room = sql_select("SELECT * FROM `Groups` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
        if (count($room) > 0) {
          list ($room) = $room;
          $privileges = sql_select("SELECT `Privileges`.*, `GroupPrivileges`.`group_id` FROM `Privileges` LEFT OUTER JOIN `GroupPrivileges` ON (`Privileges`.`id` = `GroupPrivileges`.`privilege_id` AND `GroupPrivileges`.`group_id`=" . sql_escape($id) . ") ORDER BY `Privileges`.`name`");
          $privileges_html = "";
          foreach ($privileges as $priv)
            $privileges_html .= sprintf(
                '<tr><td><input type="checkbox" '
                . 'name="privileges[]" value="%s" %s />'
                . '</td> <td>%s</td> <td>%s</td></tr>',
                $priv['id'],
                ($priv['group_id'] != ""
                    ? 'checked="checked"'
                    : ''),
                $priv['name'],
                $priv['desc']
            );

          $html .= template_render('../templates/admin_groups_edit_form.html', array (
            'link' => page_link_to("admin_groups"),
            'id' => $id,
            'privileges' => $privileges_html
          ));
        } else
          return error("No Group found.", true);
        break;

      case 'save' :
        if (isset ($_REQUEST['id']) && preg_match("/^-[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Groups ID.", true);

        $room = sql_select("SELECT * FROM `Groups` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
        if (!is_array($_REQUEST['privileges']))
          $_REQUEST['privileges'] = array ();
        if (count($room) > 0) {
          list ($room) = $room;
          sql_query("DELETE FROM `GroupPrivileges` WHERE `group_id`=" . sql_escape($id));
          $privilege_names = array();
          foreach ($_REQUEST['privileges'] as $priv) {
            if (preg_match("/^[0-9]{1,}$/", $priv)) {
              $group_privileges_source = sql_select("SELECT * FROM `Privileges` WHERE `id`=" . sql_escape($priv) . " LIMIT 1");
              if(count($group_privileges_source) > 0) {
                sql_query("INSERT INTO `GroupPrivileges` SET `group_id`=" . sql_escape($id) . ", `privilege_id`=" . sql_escape($priv));
                $privilege_names[] = $group_privileges_source[0]['name'];
              }
            }
          }
          engelsystem_log("Group privileges of group " . $room['Name'] . " edited: " . join(", ", $privilege_names));
          header("Location: " . page_link_to("admin_groups"));
        } else
          return error("No Group found.", true);
        break;
    }
  }
  return $html;
}
?>
