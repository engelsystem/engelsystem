<?php
if(sql_num_query("SHOW TABLES LIKE 'NeededAngelTypes'") === 0) {
    sql_query("CREATE TABLE `NeededAngelTypes` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `room_id` int(11) DEFAULT NULL,
                  `shift_id` int(11) DEFAULT NULL,
                  `angel_type_id` int(11) NOT NULL,
                  `count` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `room_id` (`room_id`,`angel_type_id`),
                  KEY `shift_id` (`shift_id`),
                  KEY `angel_type_id` (`angel_type_id`)
                )
    ");
    $data = sql_select("SELECT * FROM `Room`");
    $res = sql_query("SHOW COLUMNS FROM `Room` LIKE 'DEFAULT_EID_%'");
    while($col = mysql_fetch_assoc($res)) {
        $tid = explode('_', $col['Field']);
        $tid = intval(array_pop($tid));
        if($col['Default'] != '0')
            sql_query("INSERT INTO `NeededAngelTypes` (`angel_type_id`, `count`) VALUES (" . $tid . ", " . intval($col['Default']) . ")");

        foreach($data as $row) {
            if($row[$col['Field']] > 0)
                sql_query("INSERT INTO `NeededAngelTypes` (`angel_type_id`, `room_id`, `count`) VALUES (" . $tid . ", " . $row['RID'] . ", " . $row[$col['Field']] . ")");
        }
        sql_query("ALTER TABLE `Room` DROP `" . $col['Field'] . "`");
    }

    $applied = true;
}
_add_index("Room", array("Name"));
?>
