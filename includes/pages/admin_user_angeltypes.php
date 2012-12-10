<?php


/**
 * Auf dieser Seite können Erzengel Engeltypen für bestimmte Nutzer freischalten, z.B. nachdem diese für die Aufgabe geschult wurden.
 */
function admin_user_angeltypes() {
  if (isset ($_REQUEST['confirm']) && test_request_int('confirm') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['confirm']) . " AND `confirm_user_id` IS NULL") > 0) {
    sql_query("UPDATE `UserAngelTypes` SET `confirm_user_id`=" . sql_escape($_SESSION['uid']) . " WHERE `id`=" . sql_escape($_REQUEST['confirm']) . " LIMIT 1");

    success("Confirmed.");
    redirect(page_link_to('admin_user_angeltypes'));
  }

  if (isset ($_REQUEST['discard']) && test_request_int('discard') && sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['discard']) . " AND `confirm_user_id` IS NULL") > 0) {
    sql_query("DELETE FROM `UserAngelTypes` WHERE `id`=" . sql_escape($_REQUEST['discard']) . " LIMIT 1");

    success("Discarded.");
    redirect(page_link_to('admin_user_angeltypes'));
  }

  $users_source = sql_select("SELECT `UserAngelTypes`.`id`, `AngelTypes`.`name`, `User`.`Nick` FROM `UserAngelTypes` JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id` JOIN `User` ON `UserAngelTypes`.`user_id`=`User`.`UID` WHERE `AngelTypes`.`restricted`=1 AND `UserAngelTypes`.`confirm_user_id` IS NULL ORDER BY `AngelTypes`.`name`");
  $users = array ();
  foreach ($users_source as $user) {
    $user['actions'] = '<a href="' . page_link_to('admin_user_angeltypes') . '&confirm=' . $user['id'] . '">confirm</a>';
    $user['actions'] .= ' | <a href="' . page_link_to('admin_user_angeltypes') . '&discard=' . $user['id'] . '">discard</a>';
    $users[] = $user;
  }

  return page(array (
    msg(),
    table(array (
      'Nick' => "Nick",
      'name' => "Angeltype",
      'actions' => ""
    ), $users)
  ));
}

/**
 * Anzeige, ob noch Engeltypen bestätigt werden müssen. Damit werden Erzengel auf jeder Seite im Kopfbereich "genervt".
 */
function admin_new_user_angeltypes() {
  global $user, $privileges;

  if (in_array("admin_user_angeltypes", $privileges)) {
    $unconfirmed_angeltypes = sql_num_query("SELECT * FROM `UserAngelTypes` JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id` WHERE `AngelTypes`.`restricted`=1 AND `UserAngelTypes`.`confirm_user_id` IS NULL LIMIT 1") > 0;

    if ($unconfirmed_angeltypes)
      return info('<a href="' . page_link_to('admin_user_angeltypes') . '">There are unconfirmed angeltypes!</a>', true);
  }
  return "";
}
?>