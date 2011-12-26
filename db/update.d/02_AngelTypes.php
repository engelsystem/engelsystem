<?php
if(_rename_table("EngelType", "AngelTypes"))
    sql_query("ALTER TABLE `AngelTypes`
                CHANGE `TID` `id` INT NOT NULL AUTO_INCREMENT,
                CHANGE `Name` `name` VARCHAR(25) NOT NULL DEFAULT '',
                DROP `Man`,
                ADD `restricted` INT(1) NOT NULL
    ");
?>
