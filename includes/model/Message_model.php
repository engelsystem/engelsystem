<?php

/**
 * Returns Message id array
 */
function Message_ids() {
  return sql_select("SELECT `id` FROM `Messages`");
}

/**
 * Returns message by id.
 *
 * @param $id message
 *          ID
 */
function Message($id) {
  $message_source = sql_select("SELECT * FROM `Messages` WHERE `id`='" . sql_escape($id) . "' LIMIT 1");
  if ($message_source === false)
    return false;
  if (count($message_source) > 0)
    return $message_source[0];
  return null;
}

/**
 * TODO: use validation functions, return new message id
 * TODO: global $user con not be used in model!
 * send message
 *
 * @param $id User
 *          ID of Reciever
 * @param $text Text
 *          of Message
 */
function Message_send($id, $text) {
  global $user;
  
  $text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($text));
  $to = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($id));
  
  if (($text != "" && is_numeric($to)) && (sql_num_query("SELECT * FROM `User` WHERE `UID`='" . sql_escape($to) . "' AND NOT `UID`='" . sql_escape($user['UID']) . "' LIMIT 1") > 0)) {
    sql_query("INSERT INTO `Messages` SET `Datum`='" . sql_escape(time()) . "', `SUID`='" . sql_escape($user['UID']) . "', `RUID`='" . sql_escape($to) . "', `Text`='" . sql_escape($text) . "'");
    return true;
  } else {
    return false;
  }
}

?>