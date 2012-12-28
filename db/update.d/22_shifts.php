<?php
if(sql_num_query("SHOW INDEX FROM `ShiftEntry` WHERE `Key_name` = 'SID' AND `Column_name` = 'TID'") == 0) {
  sql_query("ALTER TABLE `ShiftEntry` DROP INDEX `SID`, ADD INDEX `SID` ( `SID` , `TID` )");
  $applied = true;
}
