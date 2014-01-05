<?php

function UserAngelTypes_delete_all($angeltype_id) {
  return sql_query("
      DELETE FROM `UserAngelTypes`
      WHERE `angeltype_id`=" . sql_escape($angeltype_id) . "
      AND `confirm_user_id` IS NULL");
}

function UserAngelTypes_confirm_all($angeltype_id, $confirm_user) {
  return sql_query("
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=" . sql_escape($confirm_user['UID']) . "
      WHERE `angeltype_id`=" . sql_escape($angeltype_id) . "
      AND `confirm_user_id` IS NULL");
}

function UserAngelType_confirm($user_angeltype_id, $confirm_user) {
  return sql_query("
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=" . sql_escape($confirm_user['UID']) . "
      WHERE `id`=" . sql_escape($user_angeltype_id) . "
      LIMIT 1");
}

function UserAngelType_delete($user_angeltype) {
  return sql_query("
      DELETE FROM `UserAngelTypes` 
      WHERE `id`=" . sql_escape($user_angeltype['id']) . " 
      LIMIT 1");
}

function UserAngelType_create($user, $angeltype) {
  $result = sql_query("
    INSERT INTO `UserAngelTypes` SET
    `user_id`=" . sql_escape($user['UID']) . ",
    `angeltype_id`=" . sql_escape($angeltype['id']));
  if ($result === false)
    return false;
  return sql_id();
}

function UserAngelType($user_angeltype_id) {
  $angeltype = sql_select("
      SELECT *
      FROM `UserAngelTypes`
      WHERE `id`=" . sql_escape($user_angeltype_id) . "
      LIMIT 1");
  if ($angeltype === false)
    return false;
  if (count($angeltype) == 0)
    return null;
  return $angeltype[0];
}

function UserAngelType_by_User_and_AngelType($user, $angeltype) {
  $angeltype = sql_select("
      SELECT * 
      FROM `UserAngelTypes` 
      WHERE `user_id`=" . sql_escape($user['UID']) . " 
      AND `angeltype_id`=" . sql_escape($angeltype['id']) . "
      LIMIT 1");
  if ($angeltype === false)
    return false;
  if (count($angeltype) == 0)
    return null;
  return $angeltype[0];
}
?>