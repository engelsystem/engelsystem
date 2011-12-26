<?php
if(sql_num_query("DESCRIBE `FAQ` `Sprache`") === 0) {
    sql_query("ALTER TABLE `FAQ`
        ADD `Sprache` SET('de', 'en') NOT NULL,
        ADD INDEX(`Sprache`)");
    $res = sql_query("SELECT * FROM `FAQ` WHERE `Sprache` = ''");
    while($row = mysql_fetch_assoc($res)) {
        $question = explode('<br>', $row['Frage'], 2);
        $answer = explode('<br>', $row['Antwort'], 2);
        sql_query("INSERT INTO `FAQ` (`Frage`, `Antwort`, `Sprache`) VALUES ('" . sql_escape(trim($question[1])) . "', '" . sql_escape(trim($answer[1])) . "', 'en')");
        sql_query("UPDATE `FAQ` SET `Frage` = '" . sql_escape(trim($question[0])) . "', `Antwort` = '" . sql_escape(trim($answer[0])) . "', `Sprache` = 'de' WHERE `FID` = " . $row['FID']);
    }

    $applied = true;
}
?>
