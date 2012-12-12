<?php
// make the Passwort column in the User table longer to store more advanced hashes with salts
$res = sql_select("DESCRIBE `User` `Passwort`");
if ($res[0]['Type'] == 'varchar(40)') {
	sql_query("ALTER TABLE `User` CHANGE `Passwort` `Passwort` VARCHAR(128) NULL");
	$applied = true;
}
