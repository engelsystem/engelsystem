-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. September 2005 um 18:11
-- Server Version: 4.0.24
-- PHP-Version: 4.3.10-15
--
-- Datenbank: `Himmel`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `EngelType`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `EngelType`;
CREATE TABLE IF NOT EXISTS `EngelType` (
  `TID` int(11) NOT NULL auto_increment,
  `Name` varchar(25) NOT NULL default '',
  `Man` text,
  PRIMARY KEY  (`TID`),
  UNIQUE KEY `Name` (`Name`)
) TYPE=MyISAM AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `FAQ`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `FAQ`;
CREATE TABLE IF NOT EXISTS `FAQ` (
  `FID` bigint(20) NOT NULL auto_increment,
  `Frage` text NOT NULL,
  `Antwort` text NOT NULL,
  PRIMARY KEY  (`FID`)
) TYPE=MyISAM AUTO_INCREMENT=22 ;

--
-- Daten für Tabelle `FAQ`
--

INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (1, 'Komme ich als Engel billiger/kostenlos auf den Congress?<br>\r\nDo I get in cheaper / for free to the congress as an angel ?', 'Nein, jeder Engel muss normal Eintritt bezahlen.<br>\r\nNo, every angel has to pay full price.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (2, 'Was bekomme ich für meine Mitarbeit?<br>\r\nWhat do I get for helping ? \r\n', 'Jeder Engel der arbeitet bekommt ein kostenloses (Camp-)T-Shirt nach der Veranstalltung <br>\r\nEvery working angel gets a free (Camp-) shirt after the event. ');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (3, 'Wie lange muss ich als Engel arbeiten?<br>\r\nHow long do I have to work as an angel ?', 'Diese Frage ist schwer zu beantworten. Es hängt z.B. davon ab, was man macht (z.B. Workshop-Engel) und wieviele Engel wir zusammenbekommen. <br>\r\nThis is difficult to answer. It depends on what you''ll have to do (e.g. workshop angel) and how many angels will be there. ');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (6, 'Ich bin erst XX Jahre alt. Kann ich überhaupt helfen?<br>\r\nI''m only XX years old. Can I help anyway?', 'Du bist alt genug, zum Camp zu kommen? Dann bist Du alt genug zu helfen. <br>\r\nYou''re old enough to come to the Camp? So you''re old enough to help, too.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (8, 'Wer ist eigentlich alles Erzengel?<br>\r\nWho <b>are</b> the Arch-Angels?\r\n', 'Erzengel sind dieses Jahr: Daizy, Flip, Hasi, Enno, Nachtkind und SaniFox. <br>\r\nThe ArchAngels for this year are: Daizy, Flip, Hasi, Enno, Nachtkind und SaniFox. \r\n');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (9, 'Gibt es dieses Jahr wieder einen IRC-Channel für Engel?<br>\r\nWill there be an IRC-channel for angels again?', 'Ja, im IRC-Net existiert #congress-engel. Einfach mal reinschaun!<br>\r\nYes, in the IRC-net there''s #congress-engel. Just have a look!');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (10, 'Wie gehe ich mit den Besuchern um? <br>\r\nHow do I treat visitors?', 'Man soll gegenüber den Besuchern immer höflich und freundlich sein, auch wenn diese gestresst sind. Wenn man das Gefühl hat, dass man mit der Situation nicht mehr klarkommt, sollte man sich jemanden zur Unterstützung holen, bevor man selbst auch gestresst wird :-)  <br>\r\nYou should always be polite and friendly, especially if they are stressed. When you feel you can''t handle it on your own, get someone to help you out before you get so stressed yourself that you get impolite.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (11, 'Wann sind die Engelbesprechungen? <br>\r\nWhen are the angels briefings?', 'Das wird vor Ort noch festgelegt und steht im Himmelnewssystem.<br>\r\nThe information on the Angel Briefings will be in the news section of this system.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (12, 'Was muss ich noch bedenken?<br>\r\nAnything else I should know?', 'Man sollte nicht total übermüdet oder ausgehungert, wenn man einen Einsatz hat. Eine gewisse Fitness ist hilfreich.<br>\r\nYou should not be exhausted or starving when you arrive for a shift. A reasonable amount of fitness for work would be very helpful.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (13, 'Ich habe eine Frage, auf die ich in der FAQ keine Antwort gefunden habe. Wohin soll ich mich wenden? <br>\r\nI have a guestion not answered here. Who can I ask?', 'Bei weitere Fragen kannst du die Anfragen an die Erzengel Formular benutzen.<br>\r\nIf you have further questions, you can use the Questions for the ArchAngels form.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (20, 'Wer muss alles Eintritt zahlen?&lt;br&gt;\r\nWho has to pay the full entrance price?', 'Jeder. Zumindest, solange er/sie älter als 12 Jahre ist...&lt;br&gt;\r\n&lt;b&gt;Everyone&lt;/b&gt; who is at older than 12 years old.');
INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (21, 'Wurde dieses System schonmal gehackt?\\\\n\r\nHas this System ever been hacked?', 'J4, N4TUERL1CH!\\\\n\r\nY35, 0FC0UR53!');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `News`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `News`;
CREATE TABLE IF NOT EXISTS `News` (
  `ID` int(11) NOT NULL auto_increment,
  `Datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `Betreff` varchar(150) NOT NULL default '',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL default '0',
  `Treffen` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Questions`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 17. Mai 2005 um 20:09
--

DROP TABLE IF EXISTS `Questions`;
CREATE TABLE IF NOT EXISTS `Questions` (
  `QID` bigint(20) NOT NULL auto_increment,
  `UID` int(11) NOT NULL default '0',
  `Question` text NOT NULL,
  `AID` int(11) NOT NULL default '0',
  `Answer` text NOT NULL,
  PRIMARY KEY  (`QID`)
) TYPE=MyISAM COMMENT='Fragen und Antworten' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Room`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 17. Mai 2005 um 19:58
--

DROP TABLE IF EXISTS `Room`;
CREATE TABLE IF NOT EXISTS `Room` (
  `RID` int(11) NOT NULL auto_increment,
  `Name` varchar(35) NOT NULL default '',
  `Man` text,
  `FromPentabarf` char(1) NOT NULL default 'N',
  `show` char(1) NOT NULL default 'Y',
  `Number` int(11) default NULL,
  `DEFAULT_EID_1` int(11) NOT NULL default '0',
  `DEFAULT_EID_2` int(11) NOT NULL default '0',
  `DEFAULT_EID_4` int(11) NOT NULL default '0',
  `DEFAULT_EID_5` int(11) NOT NULL default '0',
  `DEFAULT_EID_6` int(11) NOT NULL default '0',
  `DEFAULT_EID_8` int(11) NOT NULL default '0',
  `DEFAULT_EID_15` int(11) NOT NULL default '0',
  `DEFAULT_EID_17` int(11) NOT NULL default '0',
  `DEFAULT_EID_18` int(11) NOT NULL default '0',
  `DEFAULT_EID_19` int(11) NOT NULL default '0',
  `DEFAULT_EID_20` int(11) NOT NULL default '0',
  `DEFAULT_EID_21` int(11) NOT NULL default '0',
  PRIMARY KEY  (`RID`)
) TYPE=MyISAM AUTO_INCREMENT=10015 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftEntry`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `ShiftEntry`;
CREATE TABLE IF NOT EXISTS `ShiftEntry` (
  `SID` int(11) NOT NULL default '0',
  `TID` int(11) NOT NULL default '0',
  `UID` int(11) NOT NULL default '0',
  `Comment` text
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Shifts`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 17. Mai 2005 um 19:55
--

DROP TABLE IF EXISTS `Shifts`;
CREATE TABLE IF NOT EXISTS `Shifts` (
  `SID` int(11) NOT NULL auto_increment,
  `DateS` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateE` datetime NOT NULL default '0000-00-00 00:00:00',
  `Len` float NOT NULL default '0',
  `RID` int(11) NOT NULL default '0',
  `Man` text,
  `FromPentabarf` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`SID`)
) TYPE=MyISAM AUTO_INCREMENT=10277 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Sprache`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 31. März 2005 um 22:23
--

DROP TABLE IF EXISTS `Sprache`;
CREATE TABLE IF NOT EXISTS `Sprache` (
  `TextID` varchar(35) NOT NULL default 'pub_sprache_',
  `Sprache` char(2) NOT NULL default 'DE',
  `Text` text NOT NULL,
  KEY `TextID` (`TextID`,`Sprache`)
) TYPE=MyISAM;

--
-- Daten für Tabelle `Sprache`
--

INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('1', 'DE', 'Hallo ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('1', 'EN', 'Hello ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('2', 'DE', ',\r\n\r\ndu bist jetzt in unserem Engelsystem angemeldet.\r\nWähle zum Abmelden bitte immer den Abmelden-Button auf der rechten Seite.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('3', 'DE', 'Neuen Eintrag erfassen...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('3', 'EN', 'Create new entry...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('4', 'EN', 'Entry saved.\r\n\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('4', 'DE', 'Eintrag wurde gesichert.\n\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('2', 'EN', ',\r\n\r\nyou are now logged in on the angelsystem.\r\nTo log out please choose the logout-button on the right side.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('5', 'DE', 'Seite: ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('5', 'EN', 'Page: ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('6', 'DE', 'Neue News erstellen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('6', 'EN', 'Create new News:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('7', 'DE', 'Betreff:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('7', 'EN', 'Subject:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('8', 'EN', 'Text:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('8', 'DE', 'Text:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('9', 'DE', 'Treffen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('9', 'EN', 'Meeting:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('10', 'DE', 'Sichern');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('10', 'EN', 'save');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('11', 'DE', 'zurück ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('11', 'EN', 'back ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('12', 'DE', 'top');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('12', 'EN', 'top ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('13', 'DE', 'auf dieser Seite kannst Du deine persönlichen Einstellungen ändern, wie zum Beispiel dein Kennwort, Farbeinstellungen usw.\r\n\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('13', 'EN', 'here you can change your personal settings i.e. password, colour settings etc.\r\n\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('14', 'DE', 'Hier kannst du dein Kennwort für unsere Himmelsverwaltung ändern. ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('14', 'EN', 'Here you can change your password.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('15', 'DE', 'Altes Passwort:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('15', 'EN', 'Old password:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('16', 'DE', 'Neues Passwort:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('16', 'EN', 'New password:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('17', 'DE', 'Passwortbestätigung:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('17', 'EN', 'password confirmation:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('18', 'DE', 'Hier kannst du dir dein Farblayout aussuchen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('18', 'EN', 'Here you can choose your colour settings:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('19', 'DE', 'Farblayout:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('19', 'EN', 'colour settings:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('20', 'DE', 'Hier kannst Du dir deine Sprache aussuchen:\r\nHere you can choose your language:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('20', 'EN', 'Here you can choose your language:\r\nHier kannst Du dir deine Sprache aussuchen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('21', 'DE', 'Sprache:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('21', 'EN', 'Language:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('22', 'DE', 'Hier kannst du dir einen Avatar aussuchen. Dies lässt neben deinem Nick z. B. in den News das Bildchen erscheinen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('22', 'EN', 'Here you can choose your avatar. It will be displayed next to your Nick. ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('23', 'DE', 'Avatar:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('23', 'EN', 'Avatar:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('24', 'DE', 'Keiner');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('24', 'EN', 'nobody');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('25', 'DE', 'Eingegebene Kennwörter sind nicht gleich -> OK.\r\nCheck ob altes Passwort ok ist:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('25', 'EN', 'Check if the incoming passwords are identic. -> OK.\r\nCheck if the old password is correct:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('26', 'DE', '-> OK.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('26', 'EN', '-> OK.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('27', 'DE', 'Setzen des neuen Kennwortes...:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('27', 'EN', 'Set new password...:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('28', 'DE', 'Neues Kennwort wurde gesetzt.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('28', 'EN', 'New password saved.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('29', 'DE', 'Ein Fehler ist aufgetreten.\r\nProbiere es nocheinmal.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('29', 'EN', 'An error has occured.\r\nPlease try again.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('30', 'DE', '-> nicht OK.\r\nBitte nocheinmal probieren.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('30', 'EN', '-> not OK.\r\nPlease try again.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('31', 'DE', 'Kennwörter sind nicht gleich. Bitte wiederholen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('31', 'EN', 'The passwords are not identic. Please try again.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('32', 'DE', 'Neues Farblayout wurde gesetzt. Mit der nächsten Seite wird es aktiv.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('32', 'EN', 'New colour settings are saved. On the next page it will be active.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('33', 'DE', 'Sprache wurde gesetzt. Mit der nächsten Seite wies es aktiv.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('33', 'EN', 'Language is saved. On the next page it will be active.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('34', 'DE', 'Avatar wurde gesetzt.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('34', 'EN', 'Avatar is saved.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('34', 'EN', 'Avatar is saved.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('35', 'DE', '<b>Neue Anfrage:</b>\r\nIn diesem Formular hast du die Möglichkeit, den Erzengeln eine Frage zu stellen. Wenn diese beantwortet ist, wirst du hier darüber informiert. Sollte die Frage von allgemeinen Interesse sein, wird diese in die Engel-FAQ übernommen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('35', 'EN', '<b>New Question:</b>\r\nWith the form you have the choice to ask your (local) Archangels. If you question is answered you will be informed (Section: answered questions).\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('36', 'DE', 'Stelle hier deine Frage');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('36', 'EN', 'Tell us your question');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('37', 'DE', 'Deine Anfrage war:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('37', 'EN', 'Your question was:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('38', 'DE', 'Diese liegt nun bei den Erzengeln zur Beantwortung.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('38', 'EN', 'This lies now with the archangels for answer.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('39', 'DE', 'Deine bisherigen Anfragen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('39', 'EN', 'Your past inquiries:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('40', 'DE', 'Offene Anfragen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('40', 'EN', 'Open inquiries:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('41', 'DE', 'keine vorhanden...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('41', 'EN', 'nothing exists...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('42', 'DE', 'Beantwortete Anfragen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('42', 'EN', 'Answered inquiries:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_pass_no_ok', 'DE', 'Dein Passwort ist nicht korrekt. Bitte probiere es nocheinmal:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_User_unset', 'DE', 'Es wurde kein User mit deinem Nick gefunden. bitte probiere es nochmal oder wende dich an die Erzengel.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_User_more_as_one', 'DE', 'Fuer deinen Nick gab es mehrere User... bitte wende dich an die Erzengel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('Hello', 'DE', 'Hallo ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('Hello', 'EN', 'Hello ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_beschreibung', 'DE', 'hier kannst du dich f&uuml;r Schichten eintragen. Dazu w&auml;hle such dir eine freie Schicht und klicke auf den Link! Du kannst dir eine Schicht &uuml;ber den Raum bzw. Datum aussuchen. W&auml;hle hierf&uuml;r einen Tag / ein Datum aus.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_auswahl_raeume', 'DE', 'Zur Auswahl stehende R&auml;ume:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_1', 'DE', 'Und nat&uuml;rlich kannst du dir auch ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_2', 'DE', 'alles ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_3', 'DE', 'auf einmal anzeigen lassen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_1', 'DE', 'Anzeige des Schichtplans am ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_2', 'DE', ' im Raum: ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_3', 'DE', 'Anzeige des Schichtplans f&uuml;r den ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_engel', 'DE', 'engel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_engel', 'EN', 'engel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_ist', 'DE', 'ist');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_sind', 'DE', 'sind');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_weitere', 'DE', ' weitere');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_weiterer', 'DE', ' weiterer');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_werden', 'DE', ' werden ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_wird', 'DE', ' wird ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_noch_gesucht', 'DE', ' noch gesucht');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_und', 'DE', ' und ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_beschreibung', 'DE', 'hier kannst du dich zum Wecken eintragen. Dazu sage einfach wann und wo und der Engel vom Dienst wird dich wecken.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_beschreibung2', 'DE', 'Deine bisherigen eingetragenen Zeiten:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Datum', 'DE', 'Datum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Ort', 'DE', 'Ort');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Bemerkung', 'DE', 'Bermerkung');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_change', 'EN', 'delete');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Text2', 'DE', 'Hier kannst du einen neuen Eintrag erfassen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_bouton', 'DE', 'Weck mich!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_bouton', 'EN', 'wake me up!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_del', 'EN', 'delete');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung1', 'DE', 'hier siehst du die Schichten, f&uuml;r die du dich eingetragen hast.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung2', 'DE', 'Bitte versuche, p&uuml;nktlich zu den Schichten zu erscheinen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung3', 'DE', 'Hier hast du auch die M&ouml;glichkeit, dich bis ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung4', 'DE', ' Stunden vor Schichtbeginn auszutragen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_anzahl1', 'DE', 'Du hast dich f&uuml;r ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_anzahl2', 'DE', ' Schichten eingetragen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Datum', 'DE', 'Datum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Uhrzeit', 'DE', 'Uhrzeit');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Ort', 'DE', 'Ort');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Bemerkung', 'DE', 'Bemerkung');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_austragen', 'DE', 'austragen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_austragen_n_c', 'EN', 'no longer possible');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_delate1', 'DE', 'Schicht wird ausgetragen...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_add_ok', 'DE', 'Schicht wurde ausgetragen.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_add_ko', 'DE', 'Sorry, ein kleiner Fehler ist aufgetreten... probiere es doch bitte nocheinmal :)');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_after', 'DE', 'zu sp&auml;t');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_pass_no_ok', 'EN', 'Your password is not correct.  Please try it again:\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_User_unset', 'EN', 'No user was found with that Nickname.  Please try again.  If you are still having problems, see an ArchAngel\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_index_User_more_as_one', 'EN', 'This nickname is registered for more than one user, please contact an ArchAngel.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_beschreibung', 'EN', 'Here, you can register for shifts.  To do this, please choose an empty shift, and click the link.  You can choose the place, time and date of the shift. You can choose the date at the right.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_auswahl_raeume', 'EN', 'Here, please choose the area you want to work in.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_1', 'EN', 'And naturally you can also choose to show\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_2', 'EN', 'everything');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_alles_3', 'EN', ' at once.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_auswahl_raeume', 'EN', 'To the selection of available areas.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_1', 'EN', 'Show the shift schedule\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_2', 'EN', ' in Area: ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_Anzeige_3', 'EN', 'Show the shift schedule for\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_ist', 'EN', 'is');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_sind', 'EN', 'are ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_beschreibung', 'EN', 'Here you can register for a wake-up "call".  Simply say when and where the angel should come to wake you.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_weitere', 'EN', ' more');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_weiterer', 'EN', ' more');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_werden', 'EN', ' are ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_wird', 'EN', ' is  ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_noch_gesucht', 'EN', ' still needed ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_und', 'EN', ' and ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_beschreibung2', 'EN', 'The wake-up calls you have ordered:\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Datum', 'EN', 'Date');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Ort', 'EN', 'Place');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_change', 'EN', 'delete');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Bemerkung', 'EN', 'Notes');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_change', 'DE', 'löschen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_del', 'DE', 'löschen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_wake_Text2', 'EN', 'Schedule a new wake-up here::');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung1', 'EN', 'Here are the shifts that you have signed up for.\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung2', 'EN', 'Please try to arrive for your shift on time.  Be punctual!\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung3', 'EN', 'Here you can remove yourself from a shift up to\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_beschreibung4', 'EN', ' hours before your shift is scheduled to begin.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_anzahl1', 'EN', 'You have signed up for ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_anzahl2', 'EN', ' shift(s) so far');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Datum', 'EN', 'Date');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Uhrzeit', 'EN', 'Time');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Ort', 'EN', 'Place');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Bemerkung', 'EN', 'Notes');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Error', 'EN', 'One error war occurred');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_austragen', 'EN', 'remove');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_austragen_n_c', 'EN', 'is no longer possible');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_austragen_n_c', 'DE', 'nicht mehr m&ouml;glich');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_delate1', 'EN', 'Shift is being removed...');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_add_ok', 'EN', 'Shift has been removed.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_add_ko', 'EN', 'Sorry, something went wrong somewhere.  Please try it again. :)\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_after', 'EN', 'sorry, too late!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text1', 'DE', 'Hallo liebe Chaoten willkommen an der Himmelspforte! ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text2', 'DE', 'Ich bin Gabriel und muss jetzt entscheiden, ob Du Engel oder Daemon\r\nbist.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text1', 'EN', 'Hello, Chaos-guys, welcome to the Gate of Heaven!\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text3', 'DE', 'Dazu beantworte mir bitte folgende Fragen:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text2', 'EN', 'I''m Gabriel and must decide now, if you are an angel or a daemon.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text4', 'EN', 'Please note: You have to activate cookies!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text4', 'DE', 'Achtung: Cookies müssen aktiviert sein');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text3', 'EN', 'Please answer the following questions:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_nick', 'DE', 'Wie ist Dein Nick:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_pass', 'DE', 'Wie ist Dein Passwort:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_send', 'DE', 'mach mal Gabriel!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_nick', 'EN', 'What is your Loginname:\r\n');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_pass', 'EN', 'What is your password:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_logout', 'DE', 'Du wurdest erfolgreich abgemeldet.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_logout', 'EN', 'You have been successfully logged out.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_index', 'DE', 'Index');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_FAQ', 'DE', 'FAQ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_plan', 'DE', 'Lageplan');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_index', 'EN', 'Index');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_FAQ', 'EN', 'FAQ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_menuname', 'DE', 'Men&uuml;');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_plan', 'EN', 'Map');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_news', 'EN', 'News');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_news', 'DE', 'News');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Engelbesprechung', 'DE', 'Engelbesprechung');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_menuname', 'EN', 'Menu');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Schichtplan', 'DE', 'Schichtplan');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Wecken', 'DE', 'Wecken');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_mySchichtplan', 'DE', 'Mein Schichtplan');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_questionEngel', 'DE', 'Anfragen an die Erzengel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Einstellungen', 'DE', 'Einstellungen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Engelbesprechung', 'EN', 'Angel meeting');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Abmelden', 'DE', 'Abmelden');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Schichtplan', 'EN', 'Available Shifts');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Wecken', 'EN', 'Wake-up Service');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_lang_send', 'EN', 'do it Gabriel!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_mySchichtplan', 'EN', 'My Shifts');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_questionEngel', 'EN', 'Questions for the ArchAngels');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Abmelden', 'EN', 'Logout');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Einstellungen', 'EN', 'Options');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_Name', 'DE', 'Himmel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_Name', 'EN', 'Heaven');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_MakeUser', 'DE', 'Benutzer Anlegen');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('menu_MakeUser', 'EN', 'Create a new accont');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Waeckerlist', 'DE', 'Weckerlist');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_Waeckerlist', 'EN', 'Wake-up list');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Text1', 'DE', 'dies ist die Weckliste. Schaue hier bitte, wann die Leute geweckt werden wollen und erledige dies... schliesslich willst du bestimmt nicht deren Schichten uebernehmen :-)\r\n<br><br>\r\nDie bisherigen eingetragenen Zeiten:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Nick', 'DE', 'Nick');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Nick', 'EN', 'Nick');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Datum', 'DE', 'Datum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Datum', 'EN', 'Date');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Ort', 'DE', 'Ort');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Ort', 'EN', 'Place');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Comment', 'DE', 'Bemerkung');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Comment', 'EN', 'Comment');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_waeckliste_Text1', 'EN', 'this is the wack-up list. Peace show hire, how wont to wack-up and wack up this person... schliesslich willst du bestimmt nicht deren Schichten uebernehmen :-)\r\n<br><br>\r\nShow all entreys:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('nonpublic/waeckliste.php', 'DE', 'Weckdienst - Liste der zu weckenden Engel');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('nonpublic/waeckliste.php', 'EN', 'Wackup list - list of the to wackup engels');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_ToManyYousers', 'DE', 'FEHLER: Es wurden keine weiteren Engel benötigt !!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_ToManyYousers', 'EN', 'ERROR: There are enogh Engels for this chip');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Len', 'DE', 'Länge');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_mywake_Len', 'EN', 'lenght');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_AllreadyinShift', 'DE', 'du bist bereits in einer Schicht eingetragen!');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_AllreadyinShift', 'EN', 'you are at this time entrit in another shift');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Error', 'DE', 'Ein Fehler ist aufgetreten');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_WriteOK', 'DE', 'Du bist jetzt der Schicht zugeteilt. Vielen Dank für deine Mitarbeit.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Text1', 'DE', 'Hier kannst du dich in eine Schicht eintragen. Als Kommentar kannst du etwas x-belibiges eintragen, wie z. B.\r\nwelcher Vortrag dies ist oder ähnliches. Den Kommentar kannst nur du sehen. ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Date', 'DE', 'Datum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Place', 'DE', 'Ort');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Job', 'DE', 'Aufgabe');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Len', 'DE', 'Dauer');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_TextFor', 'DE', 'Text zur Schicht');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Comment', 'DE', 'Dein Kommentar');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_submit', 'DE', 'Ja, ich will helfen..."');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text5', 'DE', 'Bitte überprüfen Sie den SSL Key');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('index_text5', 'EN', 'Please check your SSL-Key:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_Edit_Text1', 'DE', 'Hier könnt ihr euren Kommentar ändern:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_Text1', 'DE', 'Text wird gespeichert');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_OK', 'DE', 'erfolgreich gespeichert.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_KO', 'DE', 'Fehler beim speichern');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_text1', 'DE', 'hir kanst du die übersetzten text bearbeiten.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_text1', 'EN', 'hire can you edit the text of the engelsystem');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_TextID', 'EN', 'TextID');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_TextID', 'DE', 'TextID');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_Sprache', 'DE', 'Sprache ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_Sprache', 'EN', 'Language ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Place', 'EN', 'place');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_Edit', 'DE', 'Bearbeiten');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_sprache_Edit', 'EN', 'edit');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Date', 'EN', 'Date');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_KO', 'EN', 'save KO');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_OK', 'EN', 'save OK');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_EditSave_Text1', 'EN', 'Text was saved');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_myshift_Edit_Text1', 'EN', 'Here can you change your comment:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Comment', 'EN', 'Your comment');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text1', 'DE', 'Diese Funktion ermöglicht es den Erzengeln, schnell die Engel mit einer vorgebbaren Anzahl an der Stunden als Aktiv zu markieren.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text1', 'EN', 'This Funktion enabled the erzengels, to set engels as Active, who has enough hours worked.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text2', 'DE', 'Über die Engelliste kann dies für einzelne Engel erledigt werden.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text2', 'EN', 'Over the engellist can you do this for singel engels.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text31', 'DE', 'Alle Engel mit mindestens');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text31', 'EN', 'All engels with at least');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text32', 'DE', 'Schichten als Aktiv markieren');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text32', 'EN', 'mark shifts as "Activ"');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Nick', 'DE', 'Nick');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Nick', 'EN', 'Nick');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Anzahl', 'DE', 'Anzahl Schichten');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Anzahl', 'EN', 'number of shifts');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Time', 'DE', 'Gesamtzeit');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Time', 'EN', 'summery time');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_submit', 'EN', 'Yes, I will help..."');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Len', 'EN', 'len');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Job', 'EN', 'job');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text5_1', 'DE', 'Alle Engel mit mindestens ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text5_1', 'EN', 'All engels with at least ');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text5_2', 'DE', ' Schichten werden jetzt als "Aktiv" markiert');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Text5_2', 'EN', ' shifs was market as "active"');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Active', 'DE', 'Aktiv');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_aktive_Active', 'EN', 'active');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_TextFor', 'EN', 'text for shift');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_WriteOK', 'EN', 'You was written for the shift as an engel. Thank you for your cooperation.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_add_Text1', 'EN', 'Hire can you entry you for a shift. As commend can you written wat you wont, it is only for you.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_colision', 'DE', '<h1>Fehler</h1>\r\nÜberschneidung von schichten:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schichtplan_colision', 'EN', '<h1>error</h1>\r\noverlap on shift:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_EmptyShifts', 'DE', 'Die n&auml;sten 15 freien Schichten:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_schicht_EmptyShifts', 'EN', 'The next 15 empty shifts:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_date', 'DE', 'Datum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_date', 'EN', 'Date');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_time', 'DE', 'Zeit');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_time', 'EN', 'Time');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_room', 'DE', 'Raum');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_room', 'EN', 'room');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_commend', 'DE', 'Kommentar');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('inc_schicht_commend', 'EN', 'comment');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Name', 'DE', 'Nachname:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Name', 'EN', 'Lastname:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Nick', 'DE', 'Nick:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Nick', 'EN', 'nick:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Vorname', 'DE', 'Vorname:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Vorname', 'EN', 'first name:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Alter', 'DE', 'Alter:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Alter', 'EN', 'Age:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Telefon', 'DE', 'Telefon:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Telefon', 'EN', 'Phone:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Handy', 'DE', 'Handy:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Handy', 'EN', 'Mobile Phone:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_DECT', 'DE', 'DECT:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_DECT', 'EN', 'DECT:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_email', 'DE', 'E-Mail:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_email', 'EN', 'email:');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Text_UserData', 'EN', 'Here you can change your user details.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_UserDateSaved', 'DE', 'Deine Beschreibung für unsere Himmelsverwaltung wurde ändern.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_UserDateSaved', 'EN', 'Your user details was saved.');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_SchichtplanBeamer', 'DE', 'Schischtplan für Beamer optimiert');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_menu_SchichtplanBeamer', 'EN', 'Shifts for beamer optimice');
INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES ('pub_einstellungen_Text_UserData', 'DE', 'Hier kannst du deine Beschreibung für unsere Himmelsverwaltung ändern.');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 10. September 2005 um 18:05
-- Letzter Check am: 03. September 2005 um 11:48
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
  `UID` int(11) NOT NULL auto_increment,
  `Nick` varchar(23) NOT NULL default '',
  `Name` varchar(23) default NULL,
  `Vorname` varchar(23) default NULL,
  `Alter` int(4) default NULL,
  `Telefon` varchar(40) default NULL,
  `DECT` varchar(4) default NULL,
  `Handy` varchar(40) default NULL,
  `email` varchar(123) default NULL,
  `Size` varchar(4) default NULL,
  `Passwort` varchar(40) default NULL,
  `Gekommen` tinyint(4) NOT NULL default '0',
  `Aktiv` tinyint(4) NOT NULL default '0',
  `Tshirt` tinyint(4) default '0',
  `color` tinyint(4) default '1',
  `Sprache` char(2) default 'EN',
  `Avatar` int(11) default '0',
  `lastLogIn` datetime default NULL,
  `Art` varchar(30) default NULL,
  `kommentar` text,
  PRIMARY KEY  (`UID`,`Nick`),
  UNIQUE KEY `Nick` (`Nick`)
) TYPE=MyISAM AUTO_INCREMENT=274 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserCVS`
--
-- Erzeugt am: 25. März 2005 um 13:14
-- Aktualisiert am: 25. März 2005 um 13:14
--

DROP TABLE IF EXISTS `UserCVS`;
CREATE TABLE IF NOT EXISTS `UserCVS` (
  `UID` int(11) NOT NULL default '0',
  `MenueShowAdminSection` char(1) NOT NULL default 'N',
  `logout.php` char(1) NOT NULL default 'Y',
  `nonpublic/index.php` char(1) NOT NULL default 'Y',
  `nonpublic/news.php` char(1) NOT NULL default 'Y',
  `nonpublic/newsAddMeting` char(1) NOT NULL default 'N',
  `nonpublic/news_comments.php` char(1) NOT NULL default 'Y',
  `nonpublic/myschichtplan.php` char(1) NOT NULL default 'Y',
  `nonpublic/engelbesprechung.php` char(1) NOT NULL default 'Y',
  `admin/index.php` char(1) NOT NULL default 'N',
  `nonpublic/schichtplan.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan_add.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan_beamer.php` char(1) NOT NULL default 'Y',
  `nonpublic/wecken.php` char(1) NOT NULL default 'N',
  `nonpublic/waeckliste.php` char(1) NOT NULL default 'N',
  `nonpublic/faq.php` char(1) NOT NULL default 'Y',
  `nonpublic/einstellungen.php` char(1) NOT NULL default 'Y',
  `admin/list.php` char(1) NOT NULL default 'N',
  `admin/dbUpdateFromXLS.php` char(1) NOT NULL default 'N',
  `admin/room.php` char(1) NOT NULL default 'N',
  `admin/EngelType.php` char(1) NOT NULL default 'N',
  `admin/schichtplan.php` char(1) NOT NULL default 'N',
  `admin/shiftadd.php` char(1) NOT NULL default 'N',
  `admin/schichtplan_druck.php` char(1) NOT NULL default 'N',
  `admin/user.php` char(1) NOT NULL default 'N',
  `admin/user2.php` char(1) NOT NULL default 'N',
  `admin/aktiv.php` char(1) NOT NULL default 'N',
  `admin/tshirt.php` char(1) NOT NULL default 'N',
  `admin/news.php` char(1) NOT NULL default 'N',
  `admin/faq.php` char(1) NOT NULL default 'N',
  `admin/free.php` char(1) NOT NULL default 'N',
  `admin/sprache.php` char(1) NOT NULL default 'N',
  `admin/dect.php` char(1) NOT NULL default 'N',
  `Netz` char(1) NOT NULL default 'N',
  `Kassen` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`UID`,`UID`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Wecken`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `Wecken`;
CREATE TABLE IF NOT EXISTS `Wecken` (
  `ID` int(11) NOT NULL auto_increment,
  `UID` int(11) NOT NULL default '0',
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `Ort` text NOT NULL,
  `Bemerkung` text NOT NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news_comments`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 25. März 2005 um 12:16
--

DROP TABLE IF EXISTS `news_comments`;
CREATE TABLE IF NOT EXISTS `news_comments` (
  `ID` bigint(11) NOT NULL auto_increment,
  `Refid` int(11) NOT NULL default '0',
  `Datum` datetime NOT NULL default '0000-00-00 00:00:00',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Refid` (`Refid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

