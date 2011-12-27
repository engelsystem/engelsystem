<?php
if(sql_num_query("SHOW COLUMNS FROM `Shifts` LIKE 'Date_'") == 2) {
    if(sql_num_query("DESCRIBE `Shifts` `Len`") != 0) {
        if(sql_num_query("SELECT * FROM `Shifts` WHERE DATE_SUB(`DateE`, INTERVAL (`Len`*60) MINUTE) != `DateS`") != 0)
            die("Inconsistent data in Shifts table, won't do update " . __FILE__);
        else {
            sql_query("ALTER TABLE `Shifts` DROP `Len`");
        }
    }
    _datetime_to_int("Shifts", "DateS");
    _datetime_to_int("Shifts", "DateE");
    sql_query("ALTER TABLE `Shifts` CHANGE `DateS` `start` INT NOT NULL, CHANGE `DateE` `end` INT NOT NULL");

    $applied = true;
}

if(sql_num_query("DESCRIBE `Shifts` `Man`") === 1 && sql_num_query("DESCRIBE `Shifts` `name`") === 0) {
    sql_query("ALTER TABLE `Shifts` CHANGE `Man` `name` VARCHAR(1024) NULL");

    $applied = true;
}

$res = sql_select("DESCRIBE `Shifts` `PSID`");
if($res[0]['Type'] == 'text') {
    sql_query("ALTER TABLE `Shifts` CHANGE `PSID` `PSID` INT NULL");

    $applied = true;
}
_add_index("Shifts", array("PSID"), "UNIQUE");
_add_index("Shifts", array("RID"));
?>
