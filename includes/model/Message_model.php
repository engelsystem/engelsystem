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
 * @param $message_id message
 *          ID
 */
function Message($message_id) {
  $message_source = sql_select("SELECT * FROM `Messages` WHERE `id`='" . sql_escape($message_id) . "' LIMIT 1");
  if ($message_source === false) {
    return false;
  }
  if (count($message_source) > 0) {
    return $message_source[0];
  }
  return null;
}

/**
 * TODO: use validation functions, return new message id
 * TODO: global $user con not be used in model!
 * send message
 *
 * @param $receiver_user_id User
 *          ID of Reciever
 * @param $text Text
 *          of Message
 */
function Message_send($receiver_user_id, $text) {
  global $user;
  
  $text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($text));
  $receiver_user_id = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($receiver_user_id));
  
  if (($text != "" && is_numeric($receiver_user_id)) && (sql_num_query("SELECT * FROM `User` WHERE `UID`='" . sql_escape($receiver_user_id) . "' AND NOT `UID`='" . sql_escape($user['UID']) . "' LIMIT 1") > 0)) {
    sql_query("INSERT INTO `Messages` SET `Datum`='" . sql_escape(time()) . "', `SUID`='" . sql_escape($user['UID']) . "', `RUID`='" . sql_escape($receiver_user_id) . "', `Text`='" . sql_escape($text) . "'");
    return true;
  }
  
  return false;
}

?>