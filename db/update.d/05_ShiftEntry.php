<?php
if(sql_num_query("DESCRIBE `ShiftEntry` `id`") === 0) {
    sql_query("ALTER TABLE `ShiftEntry` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    $applied = true;
}
_add_index("ShiftEntry", array("SID"));
_add_index("ShiftEntry", array("TID"));
_add_index("ShiftEntry", array("UID"));
?>
