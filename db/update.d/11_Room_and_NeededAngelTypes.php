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

if(sql_num_query("SELECT * FROM `ShiftEntry` WHERE `UID` = 0")) {
    $data = sql_query("
        INSERT INTO `NeededAngelTypes` (`shift_id`, `angel_type_id`, `count`)
            SELECT se.`SID`, se.`TID`, se.`count` FROM (
                SELECT `SID`, `TID`, COUNT(`TID`) AS `count`
                    FROM `ShiftEntry`
                    GROUP BY `SID`, `TID`
                ) AS se
                INNER JOIN `Shifts` AS s ON s.`SID` = se.`SID`
                INNER JOIN `Room` AS r ON s.`RID` = r.`RID`
                LEFT JOIN `NeededAngelTypes` AS nat ON (nat.`room_id` = r.`RID` AND nat.`angel_type_id` = se.`TID`)
                WHERE nat.`count` IS NULL OR nat.`count` != se.`count`
    ");

    sql_query("DELETE FROM `ShiftEntry` WHERE `UID` = 0 AND `Comment` IS NULL");

    $applied = true;
}
_add_index("Room", array("Name"));
?>
