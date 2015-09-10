<?php
// there have been some new translations added.
// For each of them, check if we already got it and create it if not
// We can conviniently do this with "INSERT IGNORE" and a UNIQUE key:

$res = sql_select("SHOW INDEX FROM `Sprache` WHERE `Key_name` = 'TextID'");
if($res[0]['Non_unique'] != 0) {
    sql_query("ALTER TABLE `Sprache` DROP INDEX `TextID`, ADD UNIQUE (`TextID`, `Sprache`)");
    $applied = true;
}

$res = mysql_query("INSERT IGNORE INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES
('no_access_text', 'DE', 'Du hast keinen Zugriff auf diese Seite. Vermutlich muss du dich erst anmelden/registrieren!'),
('no_access_text', 'EN', 'You don't have permission to view this page. You probably have to sign in or register in order to gain access!'),
('no_access_title', 'DE', 'Kein Zugriff'),
('no_access_title', 'EN', 'No Access'),
('rooms', 'DE', 'Orte'),
('rooms', 'EN', 'locations'),
('days', 'DE', 'Tage'),
('days', 'EN', 'days'),
('tasks', 'DE', 'Aufgaben'),
('tasks', 'EN', 'tasks'),
('occupancy', 'DE', 'Belegung'),
('occupancy' ,'EN', 'occupancy'),
('all', 'DE', 'alle'),
('all', 'EN', 'all'),
('none', 'DE', 'keine'),
('none', 'EN', 'none'),
('entries', 'DE', 'Einträge'),
('entries', 'EN', 'entries'),
('time', 'DE', 'Zeit'),
('time', 'EN', 'time'),
('room', 'DE', 'Ort'),
('room' ,'EN', 'location'),
('to_filter', 'DE', 'filtern'),
('to_filter', 'EN', 'filter'),
('pub_schichtplan_tasks_notice', 'DE', 'Die hier angezeigten Aufgaben werden durch die Präferenzen in deinen Einstellungen beeinflusst!'),
('pub_schichtplan_tasks_notice', 'EN', 'The tasks shown here are influenced by the preferences you defined in your settings!'),
('inc_schicht_ical_text', 'DE', 'Zum Abonnieren der angezeigten Schichten in deiner Kalender-Software benutze <a href=\"%s\">diesen Link</a> (bitte geheimhalten, im Notfall Deinen <a href=\"%s\">iCal-Key zurücksetzen</a>):'),
('inc_schicht_ical_text', 'EN', 'To subscribe the shifts shown in your calendar software, use <a href=\"%s\">this link</a> (please keep secret, otherwise <a href=\"%s\">reset the ical key</a>):'),
('helpers', 'DE', 'Helfer'),
('helpers', 'EN', 'helpers'),
('helper', 'DE', 'Helfer'),
('helper', 'EN', 'helper'),
('needed', 'DE', 'gebraucht'),
('needed', 'EN', 'needed'),
('pub_myshifts_intro', 'DE', 'Hier sind Deine Schichten.<br/>Versuche bitte <b>15 Minuten</b> vor Schichtbeginn anwesend zu sein!<br/>Du kannst Dich %d Stunden vor Schichtbeginn noch aus Schichten wieder austragen.'),
('pub_myshifts_intro', 'EN', 'These are your shifts.<br/>Please try to appear <b>15 minutes</b> before your shift begins!<br/>You can remove yourself from a shift up to %d hours before it starts.'),
('pub_myshifts_goto_shifts', 'DE', 'Gehe zum <a href=\"%s\">Schichtplan</a> um Dich für Schichten einzutragen.'),
('pub_myshifts_goto_shifts', 'EN', 'Go to the <a href=\"%s\">shifts table</a> to sign yourself up for some shifts.'),
('pub_myshifts_signed_off', 'DE', 'Du wurdest aus der Schicht ausgetragen.'),
('pub_myshifts_signed_off', 'EN', 'You have been signed off from the shift.'),
('pub_myshifts_too_late', 'DE', 'Es ist zu spät um sich aus der Schicht auszutragen. Frage ggf. den Schichtkoordinator, ob er dich austragen kann.'),
('pub_myshifts_too_late', 'EN', 'It\'s too late to sign yourself off the shift. If neccessary, as the dispatcher to do so.'),
('sign_off', 'DE', 'austragen'),
('sign_off', 'EN', 'sign off');");

if(mysql_affected_rows() > 0)
    $applied = true;
