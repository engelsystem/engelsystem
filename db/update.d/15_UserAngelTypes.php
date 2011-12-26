<?php
if(sql_num_query("SHOW TABLES LIKE 'UserAngelTypes'") === 0) {
    sql_query("CREATE TABLE `UserAngelTypes` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `user_id` INT NOT NULL ,
                `angeltype_id` INT NOT NULL ,
                `confirm_user_id` INT NULL ,
                INDEX ( `user_id` , `angeltype_id` , `confirm_user_id` )
                )");
    sql_query("INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`)
                SELECT `User`.`UID`, `AngelTypes`.`id`
                FROM `User`
                INNER JOIN `AngelTypes`
                ON TRIM(TRAILING 'Angel' FROM `User`.`Art`) = `AngelTypes`.`name`");

    $applied = true;
}
