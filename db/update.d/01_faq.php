<?php
if(sql_num_query("DESCRIBE `FAQ` `Sprache`") === 0 && sql_num_query("DESCRIBE `FAQ` `QID`") == 0) {
    sql_query("ALTER TABLE `FAQ`
        ADD `Sprache` SET('de', 'en') NOT NULL,
        ADD `QID` INT NOT NULL,
        ADD INDEX(`Sprache`)");
    $res = sql_query("SELECT * FROM `FAQ` WHERE `Sprache` = ''");
    $i = 0;
    while($row = mysql_fetch_assoc($res)) {
        $question = preg_split('#(?:<|&lt;)br(?:>|&gt;)#i', $row['Frage'], 2, PREG_SPLIT_NO_EMPTY);
        $answer = preg_split('#(?:<|&lt;)br(?:>|&gt;)#i', $row['Antwort'], 2, PREG_SPLIT_NO_EMPTY);
        if(count($question) == 2 && count($answer) == 2)
            sql_query("INSERT INTO `FAQ` (`Frage`, `Antwort`, `Sprache`, `QID`) VALUES ('" . sql_escape(trim($question[1])) . "', '" . sql_escape(trim($answer[1])) . "', 'en', $i)");
        sql_query("UPDATE `FAQ` SET `Frage` = '" . sql_escape(trim($question[0])) . "', `Antwort` = '" . sql_escape(trim($answer[0])) . "', `Sprache` = 'de', `QID` = $i WHERE `FID` = " . $row['FID']);
        $i++;
    }
    _add_index('FAQ', array('QID', 'Sprache'), 'UNIQUE');

    $applied = true;
}
?>
