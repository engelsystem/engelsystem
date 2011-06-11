<?php
function counter() {
	global $p;

	if (sql_num_query("SELECT `Anz` FROM `Counter` WHERE `URL`='" . sql_escape($p) . "'") == 0)
		sql_query("INSERT INTO `Counter` ( `URL` , `Anz` ) VALUES ('" . sql_escape($p) . "', '1');");
	else
		sql_query("UPDATE `Counter` SET `Anz` = `Anz` + 1 WHERE `URL` = '" . sql_escape($p) . "' LIMIT 1 ;");
}
?>
