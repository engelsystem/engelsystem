<?php
mysql_query("INSERT IGNORE INTO `Privileges` (`name`, `desc`) VALUES ('atom', ' Atom news export')");
$applied = mysql_affected_rows() > 0;
?>
