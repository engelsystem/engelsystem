<?php
if(sql_num_query("DESCRIBE `Messages` `id`") === 0) {
    sql_query("ALTER TABLE `Messages`
        DROP PRIMARY KEY,
        ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST
    ");
    $applied = true;
}

_add_index("Messages", array("SUID"));
_add_index("Messages", array("RUID"));
_datetime_to_int("Messages", "Datum");
_add_index("Messages", array("Datum"));
?>
