<?php

function AngelType_delete($angeltype) {
  sql_query("DELETE FROM `NeededAngelTypes` WHERE `angel_type_id`=" . sql_escape($angeltype['id']) . " LIMIT 1");
  sql_query("DELETE FROM `ShiftEntry` WHERE `TID`=" . sql_escape($angeltype['id']) . " LIMIT 1");
  sql_query("DELETE FROM `UserAngelTypes` WHERE `angeltype_id`=" . sql_escape($angeltype['id']) . " LIMIT 1");
  return sql_query("DELETE FROM `AngelTypes` WHERE `id`=" . sql_escape($angeltype['id']) . " LIMIT 1");
}

function AngelType_update($angeltype_id, $name, $restricted) {
  return sql_query("
      UPDATE `AngelTypes` SET 
      `name`='" . sql_escape($name) . "', 
      `restricted`=" . sql_escape($restricted) . " 
      WHERE `id`=" . sql_escape($angeltype_id) . " 
      LIMIT 1");
}

function AngelType_create($name, $restricted) {
  $result = sql_query("
      INSERT INTO `AngelTypes` SET 
      `name`='" . sql_escape($name) . "', 
      `restricted`=" . sql_escape($restricted));
  if ($result === false)
    return false;
  return sql_id();
}

/**
 * Validates a name for angeltypes.
 * Returns array containing validation success and validated name.
 *
 * @param string $name          
 * @param AngelType $angeltype          
 */
function AngelType_validate_name($name, $angeltype) {
  $name = strip_item($name);
  if ($name == "")
    return array(
        false,
        $name 
    );
  if (isset($angeltype) && isset($angeltype['id']))
    return array(
        sql_num_query("
        SELECT * 
        FROM `AngelTypes` 
        WHERE `name`='" . sql_escape($name) . "' 
        AND NOT `id`=" . sql_escape($angeltype['id']) . "
        LIMIT 1") == 0,
        $name 
    );
  else
    return array(
        sql_num_query("
        SELECT `id` 
        FROM `AngelTypes` 
        WHERE `name`='" . sql_escape($name) . "' 
        LIMIT 1") == 0,
        $name 
    );
}

/**
 * Returns all angeltypes and subscription state to each of them for given user.
 *
 * @param User $user          
 */
function AngelTypes_with_user($user) {
  return sql_select("
      SELECT `AngelTypes`.*, 
      `UserAngelTypes`.`id` as `user_angeltype_id`,
      `UserAngelTypes`.`confirm_user_id` 
      FROM `AngelTypes` 
      LEFT JOIN `UserAngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id` 
      AND `UserAngelTypes`.`user_id`=" . $user['UID'] . "
      ORDER BY `name`");
}

/**
 * Returns AngelType id array
 */
function mAngelTypeList() {
  $angelType_source = sql_select("SELECT `id` FROM `AngelTypes`");
  if ($angelType_source === false)
    return false;
  if (count($angelType_source) > 0)
    return $angelType_source;
  return null;
}

/**
 * Returns angelType by id.
 *
 * @param $id angelType
 *          ID
 */
function mAngelType($id) {
  $angelType_source = sql_select("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
  if ($angelType_source === false)
    return false;
  if (count($angelType_source) > 0)
    return $angelType_source[0];
  return null;
}

?>