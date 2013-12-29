<?php

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
 * @param $id angelType ID
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