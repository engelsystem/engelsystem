<?php
_datetime_to_int("User", "lastLogin");

if(sql_num_query("DESCRIBE `User` `ical_key`") === 0) {
    sql_query("ALTER TABLE `User` ADD `ical_key` VARCHAR( 32 ) NOT NULL");
#    _add_index("User", array("ical_key"), "UNIQUE");
# XXX: not everybody has an ical_key, why?

    $applied = true;
}

$res = sql_select("DESCRIBE `User` `DECT`");
if($res[0]['Type'] == 'varchar(4)') {
    sql_query("ALTER TABLE `User` CHANGE `DECT` `DECT` VARCHAR(5) NULL");

    $applied = true;
}
?>
