<?php

/**
 * Returns Message id array
 */
function mMessageList() {
	$message_source = sql_select("SELECT `id` FROM `Messages`");
	if ($message_source === false)
		return false;
	if (count($message_source) > 0)
		return $message_source;
	return null;
}

/**
 * Returns message by id.
 *
 * @param $id message ID
 */
function mMessage($id) {
	$message_source = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
	if ($message_source === false)
		return false;
	if (count($message_source) > 0)
		return $message_source[0];
	return null;
}

?>