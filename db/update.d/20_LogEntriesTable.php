<?php

if(sql_num_query("SHOW TABLES LIKE 'LogEntries'") == 0) {
  sql_query("CREATE TABLE `LogEntries` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `timestamp` INT NOT NULL ,
      `nick` VARCHAR( 23 ) NOT NULL ,
      `message` TEXT NOT NULL ,
      INDEX ( `timestamp` )
  ) ENGINE = InnoDB;");
  $applied = true;
}

if(sql_num_query("SHOW TABLES LIKE 'ChangeLog'") == 0) {
  sql_query("DROP TABLE `ChangeLog`");
  $applied = true;
}

?>