<?php
// one translation pair added last commit was faulty (contained a closing :
// even though it should have been a .), we fix it now
mysql_query("UPDATE `Sprache`
SET `Text` = CONCAT(SUBSTR(`Text`, 1, CHAR_LENGTH(`Text`)-1), '.')
WHERE `TextID` = 'inc_schicht_ical_text' AND `Text` LIKE '%:';");

$applied = mysql_affected_rows() > 0;

// more translations
$res = mysql_query("INSERT IGNORE INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES
('occupied', 'DE', 'belegt'),
('occupied', 'EN', 'occupied'),
('free', 'DE', 'frei'),
('free', 'EN', 'free');");

$applied |= mysql_affected_rows() > 0;
