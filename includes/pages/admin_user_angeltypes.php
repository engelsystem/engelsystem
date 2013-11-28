<?php
function admin_user_angeltypes_title() {
  return _("Confirm angeltypes");
}

/**
 * Auf dieser Seite können Erzengel Engeltypen für bestimmte Nutzer freischalten, z.B. nachdem diese für die Aufgabe geschult wurden.
 */
function admin_user_angeltypes() {
  global $privileges;

  if (isset ($_REQUEST['confirm_all']) && test_request_int('confirm_all') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `angeltype_id`=" . sql_escape($_REQUEST['confirm_all']) . " AND `confirm_user_id` IS NULL") > 0) {
    $angel_type_source = sql_select("SELECT `AngelTypes`.* FROM `AngelTypes` WHERE `AngelTypes`.`id`=" . sql_escape($_REQUEST['confirm_all']) . " LIMIT 1");
    if(count($angel_type_source) > 0) {
      if(!isset($_REQUEST['confirmed'])) {
        return page(array(
          info("Möchtest Du wirklich alle Engel vom Typ " . $angel_type_source[0]['name'] . " freischalten?", true),
          buttons(array(
            button(page_link_to('admin_user_angeltypes'), "Abbrechen", 'cancel'),
            button(page_link_to('admin_user_angeltypes') . '&amp;confirm_all=' . $_REQUEST['confirm_all'] . '&amp;confirmed', "Ok", 'ok')
          ))
        ));
      }
      sql_query("UPDATE `UserAngelTypes` SET `confirm_user_id`=" . sql_escape($_SESSION['uid']) . " WHERE `angeltype_id`=" . sql_escape($_REQUEST['confirm_all']) . " LIMIT 1");
      engelsystem_log("Confirmed all " . $angel_type_source[0]['name']);
      success(_("Confirmed all."));
    }
    else error(_("Entry not found."));
    redirect(page_link_to('admin_user_angeltypes'));
  }

  if (isset ($_REQUEST['deny_all']) && test_request_int('deny_all') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `angeltype_id`=" . sql_escape($_REQUEST['deny_all']) . " AND `confirm_user_id` IS NULL") > 0) {
    $angel_type_source = sql_select("SELECT `AngelTypes`.* FROM `AngelTypes` WHERE `AngelTypes`.`id`=" . sql_escape($_REQUEST['deny_all']));
    if(count($angel_type_source) > 0) {
      if(!isset($_REQUEST['confirmed'])) {
        return page(array(
          info("Möchtest Du wirklich alle Engel vom Typ " . $angel_type_source[0]['name'] . " ablehnen?", true),
          buttons(array(
            button(page_link_to('admin_user_angeltypes'), "Abbrechen", 'cancel'),
            button(page_link_to('admin_user_angeltypes') . '&amp;deny_all=' . $_REQUEST['deny_all'] . '&amp;confirmed', "Ok", 'ok')
          ))
        ));
      }
      sql_query("DELETE FROM `UserAngelTypes` WHERE `confirm_user_id` IS NULL AND `angeltype_id`=" . sql_escape($_REQUEST['deny_all']));
      engelsystem_log("Denied all " . $angel_type_source[0]['name']);
      success("Denied all.");
    }
    else error("Entry not found.");
    redirect(page_link_to('admin_user_angeltypes'));
  }

  if (isset ($_REQUEST['confirm']) && test_request_int('confirm') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['confirm']) . " AND `confirm_user_id` IS NULL") > 0) {
    $user_angel_type_source = sql_select("SELECT `UserAngelTypes`.*, `User`.`Nick`, `User`.`UID`, `AngelTypes`.`name` FROM `UserAngelTypes` JOIN `User` ON `User`.`UID`=`UserAngelTypes`.`user_id` JOIN `AngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id` WHERE `UserAngelTypes`.`id`=" . sql_escape($_REQUEST['confirm']) . " LIMIT 1");
    if(count($user_angel_type_source) > 0) {
      sql_query("UPDATE `UserAngelTypes` SET `confirm_user_id`=" . sql_escape($_SESSION['uid']) . " WHERE `id`=" . sql_escape($_REQUEST['confirm']) . " LIMIT 1");
      engelsystem_log("Confirmed " . User_Nick_render($user_angel_type_source[0]) . " as " . $user_angel_type_source[0]['name']);
      success("Confirmed.");
    }
    else error("Entry not found.");
    redirect(page_link_to('admin_user_angeltypes'));
  }

  if (isset ($_REQUEST['deny']) && test_request_int('deny') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['deny']) . " AND `confirm_user_id` IS NULL") > 0) {
    $user_angel_type_source = sql_select("SELECT `UserAngelTypes`.*, `User`.`Nick`, `User`.`UID`, `AngelTypes`.`name` FROM `UserAngelTypes` JOIN `User` ON `User`.`UID`=`UserAngelTypes`.`user_id` JOIN `AngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id` WHERE `UserAngelTypes`.`id`=" . sql_escape($_REQUEST['deny']) . " LIMIT 1");
    if(count($user_angel_type_source) > 0) {
      sql_query("DELETE FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['deny']) . " LIMIT 1");
      engelsystem_log("Denied " . User_Nick_render($user_angel_type_source[0]) . " as " . $user_angel_type_source[0]['name']);
      success("Denied.");
    }
    else error("Entry not found.");
    redirect(page_link_to('admin_user_angeltypes'));
  }

  $angel_types_source = sql_select("SELECT * FROM `AngelTypes` WHERE `restricted`=1 ORDER BY `name`");
  $content = array();
  foreach($angel_types_source as $angel_type) {
    $user_angel_types_source = sql_select("SELECT `UserAngelTypes`.`id`, `User`.`Nick`, `User`.`UID` FROM `UserAngelTypes` JOIN `User` ON `UserAngelTypes`.`user_id`=`User`.`UID` WHERE `UserAngelTypes`.`angeltype_id`=" . sql_escape($angel_type['id']) . " AND `UserAngelTypes`.`confirm_user_id` IS NULL ORDER BY `User`.`Nick`");
    if(count($user_angel_types_source)) {
      $users = array ();
      foreach ($user_angel_types_source as $user) {
        $user['name'] = User_Nick_render($user);
        $user['actions'] = img_button(page_link_to('admin_user_angeltypes') . '&confirm=' . $user['id'], 'tick', _("confirm"));
        $user['actions'] .= '&nbsp;&nbsp;';
        $user['actions'] .= img_button(page_link_to('admin_user_angeltypes') . '&deny=' . $user['id'], 'cross', _("deny"));
        $users[] = $user;
      }
      $content[] = '<h2>' . $angel_type['name'] . ' <small>' . img_button(page_link_to('admin_user_angeltypes') . '&confirm_all=' . $angel_type['id'], 'tick', '', _("confirm all")) . ' ' . img_button(page_link_to('admin_user_angeltypes') . '&deny_all=' . $angel_type['id'], 'cross', '', _("deny all")) . '</small></h2>' . table(array (
        'name' => "Nick",
        'actions' => ""
      ), $users);
    }
  }

  return page(array (
    msg(),
    join('', $content)
  ));
}

/**
 * Anzeige, ob noch Engeltypen bestätigt werden müssen. Damit werden Erzengel auf jeder Seite im Kopfbereich "genervt", wenn zu ihren Aufgaben noch Engel bestätigt werden müssen.
 */
function admin_new_user_angeltypes() {
  global $user, $privileges;

  if (in_array("admin_user_angeltypes", $privileges)) {
    $unconfirmed_angeltypes = sql_num_query("SELECT * FROM `UserAngelTypes` JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id` WHERE `UserAngelTypes`.`angeltype_id` IN (SELECT `angeltype_id` FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']) . ") AND `AngelTypes`.`restricted`=1 AND `UserAngelTypes`.`confirm_user_id` IS NULL LIMIT 1") > 0;

    if ($unconfirmed_angeltypes)
      return info('<a href="' . page_link_to('admin_user_angeltypes') . '">' . _("There are unconfirmed angeltypes!") . '</a>', true);
  }
  return "";
}
?>
