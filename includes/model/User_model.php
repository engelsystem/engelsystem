<?php

/**
 * Returns user by id.
 * @param $id UID
 */
function User($id) {
  $user_source = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
  if(count($user_source) > 0)
    return $user_source[0];
  return null;
}

?>