<?php

function admin_groups_title() {
  return _("Grouprights");
}

function admin_groups() {
  $html = "";
  $groups = sql_select("SELECT * FROM `Groups` ORDER BY `Name`");
  if (! isset($_REQUEST["action"])) {
    $groups_table = [];
    foreach ($groups as $group) {
      $privileges = sql_select("SELECT * FROM `GroupPrivileges` JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `group_id`='" . sql_escape($group['UID']) . "'");
      $privileges_html = [];
      
      foreach ($privileges as $priv) {
        $privileges_html[] = $priv['name'];
      }
      
      $groups_table[] = [
          'name' => $group['Name'],
          'privileges' => join(', ', $privileges_html),
          'actions' => button(page_link_to('admin_groups') . '&action=edit&id=' . $group['UID'], _("edit"), 'btn-xs') 
      ];
    }
    
    return page_with_title(admin_groups_title(), [
        table([
            'name' => _("Name"),
            'privileges' => _("Privileges"),
            'actions' => '' 
        ], $groups_table) 
    ]);
  } else {
    switch ($_REQUEST["action"]) {
      case 'edit':
        if (isset($_REQUEST['id']) && preg_match("/^-[0-9]{1,11}$/", $_REQUEST['id'])) {
          $group_id = $_REQUEST['id'];
        } else {
          return error("Incomplete call, missing Groups ID.", true);
        }
        
        $group = sql_select("SELECT * FROM `Groups` WHERE `UID`='" . sql_escape($group_id) . "' LIMIT 1");
        if (count($group) > 0) {
          list($group) = $group;
          $privileges = sql_select("SELECT `Privileges`.*, `GroupPrivileges`.`group_id` FROM `Privileges` LEFT OUTER JOIN `GroupPrivileges` ON (`Privileges`.`id` = `GroupPrivileges`.`privilege_id` AND `GroupPrivileges`.`group_id`='" . sql_escape($group_id) . "') ORDER BY `Privileges`.`name`");
          $privileges_html = "";
          $privileges_form = [];
          foreach ($privileges as $priv) {
            $privileges_form[] = form_checkbox('privileges[]', $priv['desc'] . ' (' . $priv['name'] . ')', $priv['group_id'] != "", $priv['id']);
            $privileges_html .= sprintf('<tr><td><input type="checkbox" ' . 'name="privileges[]" value="%s" %s />' . '</td> <td>%s</td> <td>%s</td></tr>', $priv['id'], ($priv['group_id'] != "" ? 'checked="checked"' : ''), $priv['name'], $priv['desc']);
          }
          
          $privileges_form[] = form_submit('submit', _("Save"));
          $html .= page_with_title(_("Edit group"), [
              form($privileges_form, page_link_to('admin_groups') . '&action=save&id=' . $group_id) 
          ]);
        } else {
          return error("No Group found.", true);
        }
        break;
      
      case 'save':
        if (isset($_REQUEST['id']) && preg_match("/^-[0-9]{1,11}$/", $_REQUEST['id'])) {
          $group_id = $_REQUEST['id'];
        } else {
          return error("Incomplete call, missing Groups ID.", true);
        }
        
        $group = sql_select("SELECT * FROM `Groups` WHERE `UID`='" . sql_escape($group_id) . "' LIMIT 1");
        if (! is_array($_REQUEST['privileges'])) {
          $_REQUEST['privileges'] = [];
        }
        if (count($group) > 0) {
          list($group) = $group;
          sql_query("DELETE FROM `GroupPrivileges` WHERE `group_id`='" . sql_escape($group_id) . "'");
          $privilege_names = [];
          foreach ($_REQUEST['privileges'] as $priv) {
            if (preg_match("/^[0-9]{1,}$/", $priv)) {
              $group_privileges_source = sql_select("SELECT * FROM `Privileges` WHERE `id`='" . sql_escape($priv) . "' LIMIT 1");
              if (count($group_privileges_source) > 0) {
                sql_query("INSERT INTO `GroupPrivileges` SET `group_id`='" . sql_escape($group_id) . "', `privilege_id`='" . sql_escape($priv) . "'");
                $privilege_names[] = $group_privileges_source[0]['name'];
              }
            }
          }
          engelsystem_log("Group privileges of group " . $group['Name'] . " edited: " . join(", ", $privilege_names));
          redirect(page_link_to("admin_groups"));
        } else {
          return error("No Group found.", true);
        }
        break;
    }
  }
  return $html;
}
?>
