<?php


// create admin_user_angeltypes permission/privilege and assign it to the archangel usergroup.
if (sql_num_query("SELECT * FROM `Privileges` WHERE `name`='admin_user_angeltypes'") == 0) {
	sql_query("INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES ( NULL , 'admin_user_angeltypes', 'Confirm restricted angel types' );");
	$id = sql_id();
	sql_query("INSERT INTO `GroupPrivileges` SET `group_id`=-5, `privilege_id`='" . sql_escape($id) . "'");
	sql_query("INSERT INTO `Sprache` (
		`TextID` ,
		`Sprache` ,
		`Text`
		)
		VALUES (
		'admin_user_angeltypes', 'DE', 'Engeltypen freischalten'
		), (
		'admin_user_angeltypes', 'EN', 'Confirm angeltypes'
		);");
	$applied = true;
}
?>