-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 22. Juni 2013 um 11:05
-- Server Version: 5.1.44
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `engelsystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `AngelTypes`
--

DROP TABLE IF EXISTS `AngelTypes`;
CREATE TABLE IF NOT EXISTS `AngelTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL DEFAULT '',
  `restricted` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `AngelTypes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ChangeLog`
--

DROP TABLE IF EXISTS `ChangeLog`;
CREATE TABLE IF NOT EXISTS `ChangeLog` (
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Commend` text NOT NULL,
  `SQLCommad` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `ChangeLog`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Counter`
--

DROP TABLE IF EXISTS `Counter`;
CREATE TABLE IF NOT EXISTS `Counter` (
  `URL` varchar(255) NOT NULL DEFAULT '',
  `Anz` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`URL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Counter der Seiten';

--
-- Daten für Tabelle `Counter`
--

INSERT INTO `Counter` (`URL`, `Anz`) VALUES
('login', 4),
('news', 3),
('admin_user', 3),
('admin_groups', 2),
('admin_free', 1),
('admin_angel_types', 2),
('admin_user_angeltypes', 1),
('admin_import', 1),
('user_meetings', 1),
('user_myshifts', 3),
('user_questions', 1),
('user_settings', 6);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `FAQ`
--

DROP TABLE IF EXISTS `FAQ`;
CREATE TABLE IF NOT EXISTS `FAQ` (
  `FID` bigint(20) NOT NULL AUTO_INCREMENT,
  `Frage_de` text NOT NULL,
  `Antwort_de` text NOT NULL,
  `Frage_en` text NOT NULL,
  `Antwort_en` text NOT NULL,
  `Sprache` set('de','en') NOT NULL,
  `QID` int(11) NOT NULL,
  PRIMARY KEY (`FID`),
  KEY `Sprache` (`Sprache`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Daten für Tabelle `FAQ`
--

INSERT INTO `FAQ` (`FID`, `Frage_de`, `Antwort_de`, `Frage_en`, `Antwort_en`, `Sprache`, `QID`) VALUES
(1, 'Komme ich als Engel billiger/kostenlos auf den Congress?', 'Nein, jeder Engel muss normal Eintritt bezahlen.', 'Do I get in cheaper / for free to the congress as an angel ?', 'No, every angel has to pay full price.', '', 0),
(2, 'Was bekomme ich f&uuml;r meine Mitarbeit?', 'Jeder Engel der arbeitet bekommt ein kostenloses T-Shirt nach der Veranstalltung', 'What can i expect in return for my help?', 'Every working angel gets a free shirt after the event.', '', 0),
(3, 'Wie lange muss ich als Engel arbeiten?', 'Diese Frage ist schwer zu beantworten. Es h&auml;ngt z.B. davon ab, was man macht (z.B. Workshop-Engel) und wieviele Engel wir zusammen bekommen.', 'How long do I have to work as an angel ?', 'This is difficult to answer. It depends on what you decide to do (e.g. workshop angel) and how many people will attend.', '', 0),
(6, 'Ich bin erst XX Jahre alt. Kann ich &uuml;berhaupt helfen?', 'Wir k&ouml;nnen jede helfende Hand gebrauchen. Wenn du alt genug bist, um zum Congress zu kommen, bist du auch alt genug zu helfen.', 'I''m only XX years old. Can I help anyway?', 'We need every help we can get. If your old enough to come to the congress, your old enough to help.', '', 0),
(8, 'Wer sind eigentlich die Erzengel?', 'Erzengel sind dieses Jahr: BugBlue, TabascoEye, Jeedi, Daizy, volty', 'Who <b>are</b> the Arch-Angels?', 'The ArchAngels for this year are: BugBlue, TabascoEye, Jeedi, Daizy, volty', '', 0),
(9, 'Gibt es dieses Jahr wieder einen IRC-Channel f&uuml;r Engel?', 'Ja, im IRC-Net existiert #chaos-angel. Einfach mal reinschaun!', 'Will there be an IRC-channel for angels again?', 'Yes, in the IRC-net there''s #chaos-angel. Just have a look!', '', 0),
(10, 'Wie gehe ich mit den Besuchern um?', 'Man soll gegen&uuml;ber den Besuchern immer h&ouml;flich und freundlich sein, auch wenn diese gestresst sind. Wenn man das Gef&uuml;hl hat, dass man mit der Situation nicht mehr klarkommt, sollte man sich jemanden zur Unterst&uuml;tzung holen, bevor man selbst auch gestresst wird :-)', 'How do I treat visitors?', 'You should always be polite and friendly, especially if they are stressed. When you feel you can''t handle it on your own, get someone to help you out before you get so stressed yourself that you get impolite.', '', 0),
(11, 'Wann sind die Engelbesprechungen?', 'Das wird vor Ort noch festgelegt und steht im Himmelnewssystem.', 'When are the angels briefings?', 'The information on the Angel Briefings will be in the news section of this system.', '', 0),
(12, 'Was muss ich noch bedenken?', 'Man sollte nicht total &uuml;berm&uuml;det oder ausgehungert, wenn n man einen Einsatz hat. Eine gewisse Fitness ist hilfreich.', 'Anything else I should know?', 'You should not be exhausted or starving when you arrive for a shift. A reasonable amount of fitness for work would be very helpful.', '', 0),
(13, 'Ich habe eine Frage, auf die ich in der FAQ keine Antwort gefunden habe. Wohin soll ich mich wenden?', 'Bei weitere Fragen kannst du die Anfragen an die Erzengel Formular benutzen.', 'I have a guestion not answered here. Who can I ask?', 'If you have further questions, you can use the Questions for the ArchAngels form.', '', 0),
(20, 'Wer muss alles Eintritt zahlen?', 'Jeder. Zumindest, solange er/sie &auml;lter als 12 Jahre ist...', 'Who has to pay the full entrance price?', 'Everyone who is at older than 12 years old.', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Groups`
--

DROP TABLE IF EXISTS `Groups`;
CREATE TABLE IF NOT EXISTS `Groups` (
  `Name` varchar(35) NOT NULL,
  `UID` int(11) NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `Groups`
--

INSERT INTO `Groups` (`Name`, `UID`) VALUES
('1-Gast', -1),
('2-Engel', -2),
('3-Shift Coordinator', -3),
('5-Erzengel', -5),
('6-Developer', -6),
('4-Infodesk', -4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `LogEntries`
--

DROP TABLE IF EXISTS `LogEntries`;
CREATE TABLE IF NOT EXISTS `LogEntries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `nick` varchar(23) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `LogEntries`
--

INSERT INTO `LogEntries` (`id`, `timestamp`, `nick`, `message`) VALUES
(1, 1371897881, 'admin', 'Set new password for <a href="?p=user_myshifts&amp;id=1">admin</a>');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Messages`
--

DROP TABLE IF EXISTS `Messages`;
CREATE TABLE IF NOT EXISTS `Messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Datum` int(11) NOT NULL,
  `SUID` int(11) NOT NULL DEFAULT '0',
  `RUID` int(11) NOT NULL DEFAULT '0',
  `isRead` char(1) NOT NULL DEFAULT 'N',
  `Text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Datum` (`Datum`),
  KEY `SUID` (`SUID`),
  KEY `RUID` (`RUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Fuers interen Communikationssystem' AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `Messages`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `NeededAngelTypes`
--

DROP TABLE IF EXISTS `NeededAngelTypes`;
CREATE TABLE IF NOT EXISTS `NeededAngelTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `angel_type_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`,`angel_type_id`),
  KEY `shift_id` (`shift_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `NeededAngelTypes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `News`
--

DROP TABLE IF EXISTS `News`;
CREATE TABLE IF NOT EXISTS `News` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Datum` int(11) NOT NULL,
  `Betreff` varchar(150) NOT NULL DEFAULT '',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Treffen` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UID` (`UID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `News`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news_comments`
--

DROP TABLE IF EXISTS `news_comments`;
CREATE TABLE IF NOT EXISTS `news_comments` (
  `ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `Refid` int(11) NOT NULL DEFAULT '0',
  `Datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Refid` (`Refid`),
  KEY `UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `news_comments`
--


-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `GroupPrivileges`
--

DROP TABLE IF EXISTS `GroupPrivileges`;
CREATE TABLE IF NOT EXISTS `GroupPrivileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`,`privilege_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=203 ;

--
-- Daten für Tabelle `GroupPrivileges`
--

INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES
(187, -3, 28),
(24, -1, 5),
(200, -2, 11),
(199, -2, 26),
(23, -1, 2),
(142, -5, 16),
(141, -5, 28),
(198, -2, 9),
(197, -2, 17),
(86, -6, 21),
(140, -5, 6),
(139, -5, 12),
(196, -2, 35),
(138, -5, 14),
(137, -5, 13),
(136, -5, 7),
(195, -2, 15),
(87, -6, 18),
(194, -2, 3),
(85, -6, 10),
(193, -2, 4),
(88, -1, 1),
(186, -3, 19),
(192, -2, 30),
(109, -4, 27),
(135, -5, 31),
(184, -3, 27),
(143, -5, 5),
(144, -5, 33),
(188, -3, 16),
(185, -3, 32),
(189, -3, 33),
(191, -2, 34),
(190, -3, 25),
(201, -2, 8),
(202, -2, 24);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Privileges`
--

DROP TABLE IF EXISTS `Privileges`;
CREATE TABLE IF NOT EXISTS `Privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `desc` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

--
-- Daten für Tabelle `Privileges`
--

INSERT INTO `Privileges` (`id`, `name`, `desc`) VALUES
(1, 'start', 'Startseite für Gäste/Nicht eingeloggte User'),
(2, 'login', 'Logindialog'),
(3, 'news', 'Anzeigen der News-Seite'),
(4, 'logout', 'User darf sich ausloggen'),
(5, 'register', 'Einen neuen Engel registerieren'),
(6, 'admin_rooms', 'Räume administrieren'),
(7, 'admin_angel_types', 'Engel Typen administrieren'),
(8, 'user_settings', 'User profile settings'),
(9, 'user_messages', 'Writing and reading messages from user to user'),
(10, 'admin_groups', 'Manage usergroups and their rights'),
(11, 'user_questions', 'Let users ask questions'),
(12, 'admin_questions', 'Answer user''s questions'),
(13, 'admin_faq', 'Edit FAQs'),
(14, 'admin_news', 'Administrate the news section'),
(15, 'news_comments', 'User can comment news'),
(16, 'admin_user', 'Administrate the angels'),
(17, 'user_meetings', 'Lists meetings (news)'),
(18, 'admin_language', 'Translate the system'),
(19, 'admin_log', 'Display recent changes'),
(20, 'user_wakeup', 'User wakeup-service organization'),
(21, 'admin_import', 'Import rooms and shifts from pentabarf'),
(22, 'credits', 'View credits'),
(23, 'faq', 'View FAQ'),
(24, 'user_shifts', 'Signup for shifts'),
(25, 'user_shifts_admin', 'Signup other angels for shifts.'),
(26, 'user_myshifts', 'Allow angels to view their own shifts and cancel them.'),
(27, 'admin_arrive', 'Mark angels when they arrive.'),
(28, 'admin_shifts', 'Create shifts'),
(30, 'ical', 'iCal shift export'),
(31, 'admin_active', 'Mark angels as active and if they got a t-shirt.'),
(32, 'admin_free', 'Show a list of free/unemployed angels.'),
(33, 'admin_user_angeltypes', 'Confirm restricted angel types'),
(34, 'atom', ' Atom news export'),
(35, 'shifts_json_export', 'Export shifts in JSON format');


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Questions`
--

DROP TABLE IF EXISTS `Questions`;
CREATE TABLE IF NOT EXISTS `Questions` (
  `QID` bigint(20) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Question` text NOT NULL,
  `AID` int(11) NOT NULL DEFAULT '0',
  `Answer` text NOT NULL,
  PRIMARY KEY (`QID`),
  KEY `UID` (`UID`),
  KEY `AID` (`AID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Fragen und Antworten' AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `Questions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Room`
--

DROP TABLE IF EXISTS `Room`;
CREATE TABLE IF NOT EXISTS `Room` (
  `RID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(35) NOT NULL DEFAULT '',
  `Man` text,
  `FromPentabarf` char(1) NOT NULL DEFAULT 'N',
  `show` char(1) NOT NULL DEFAULT 'Y',
  `Number` int(11) DEFAULT NULL,
  PRIMARY KEY (`RID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `Room`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftEntry`
--

DROP TABLE IF EXISTS `ShiftEntry`;
CREATE TABLE IF NOT EXISTS `ShiftEntry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SID` int(11) NOT NULL DEFAULT '0',
  `TID` int(11) NOT NULL DEFAULT '0',
  `UID` int(11) NOT NULL DEFAULT '0',
  `Comment` text,
  PRIMARY KEY (`id`),
  KEY `TID` (`TID`),
  KEY `UID` (`UID`),
  KEY `SID` (`SID`,`TID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `ShiftEntry`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftFreeloader`
--

DROP TABLE IF EXISTS `ShiftFreeloader`;
CREATE TABLE IF NOT EXISTS `ShiftFreeloader` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Remove_Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UID` int(11) NOT NULL,
  `Length` int(11) NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `ShiftFreeloader`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Shifts`
--

DROP TABLE IF EXISTS `Shifts`;
CREATE TABLE IF NOT EXISTS `Shifts` (
  `SID` int(11) NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `RID` int(11) NOT NULL DEFAULT '0',
  `name` varchar(1024) DEFAULT NULL,
  `URL` text,
  `PSID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SID`),
  UNIQUE KEY `PSID` (`PSID`),
  KEY `RID` (`RID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `Shifts`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Sprache`
--

DROP TABLE IF EXISTS `Sprache`;
CREATE TABLE IF NOT EXISTS `Sprache` (
  `TextID` varchar(35) NOT NULL DEFAULT 'makeuser_',
  `Sprache` char(2) NOT NULL DEFAULT 'DE',
  `Text` text NOT NULL,
  UNIQUE KEY `TextID` (`TextID`,`Sprache`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `Sprache`
--

INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES
('Hallo', 'DE', 'Hallo '),
('Hallo', 'EN', 'Greetings '),
('2', 'DE', ',\r\n\r\nIm Engelsystem eingeloggt..\r\nWähle zum Abmelden bitte immer den Abmelden-Button auf der linken Seite.'),
('3', 'DE', 'Neuen Eintrag erfassen...'),
('3', 'EN', 'Create new entry...'),
('4', 'EN', 'Entry saved.\r\n\r\n'),
('4', 'DE', 'Eintrag wurde gesichert.\n\n'),
('2', 'EN', ',\r\n\r\nyou are now logged in.\r\nTo log out please choose the logout-button on the right side.'),
('5', 'DE', 'Seite: '),
('5', 'EN', 'Page: '),
('6', 'DE', 'Neue News erstellen:'),
('6', 'EN', 'Create new News:'),
('7', 'DE', 'Betreff:'),
('7', 'EN', 'Subject:'),
('8', 'EN', 'Text:'),
('8', 'DE', 'Text:'),
('9', 'DE', 'Treffen:'),
('9', 'EN', 'Meeting:'),
('save', 'DE', 'Sichern'),
('save', 'EN', 'save'),
('back', 'DE', 'zurück '),
('back', 'EN', 'back '),
('top', 'DE', 'top'),
('top', 'EN', 'top '),
('13', 'DE', 'auf dieser Seite kannst Du deine persönlichen Einstellungen ändern, wie zum Beispiel dein Kennwort, Farbeinstellungen usw.\r\n\r\n'),
('13', 'EN', 'here you can change your personal settings i.e. password, color settings etc.\r\n\r\n'),
('14', 'DE', 'Hier kannst du dein Kennwort ändern.. '),
('14', 'EN', 'Here you can change your password.'),
('15', 'DE', 'Altes Passwort:'),
('15', 'EN', 'Old password:'),
('16', 'DE', 'Neues Passwort:'),
('16', 'EN', 'New password:'),
('17', 'DE', 'Passwortbestätigung:'),
('17', 'EN', 'password confirmation:'),
('18', 'DE', 'Hier kannst du dir dein Farblayout aussuchen:'),
('18', 'EN', 'Here you can choose your color settings:'),
('19', 'DE', 'Farblayout:'),
('19', 'EN', 'color settings:'),
('20', 'DE', 'Hier kannst Du dir deine Sprache aussuchen:\r\nHere you can choose your language:'),
('20', 'EN', 'Here you can choose your language:\r\nHier kannst Du dir deine Sprache aussuchen:'),
('21', 'DE', 'Sprache:'),
('21', 'EN', 'Language:'),
('22', 'DE', 'Hier kannst du dir einen Avatar aussuchen. Dies lässt neben deinem Nick z. B. in den News das Bildchen erscheinen.'),
('22', 'EN', 'Here you can choose your avatar. It will be displayed next to your Nick. '),
('23', 'DE', 'Avatar:'),
('23', 'EN', 'Avatar:'),
('24', 'DE', 'Keiner'),
('24', 'EN', 'nobody'),
('25', 'DE', 'Eingegebene Kennwörter sind nicht gleich -> OK.\r\nCheck ob altes Passwort ok ist:'),
('25', 'EN', 'The passwords entered don''t match. -> OK.\r\nCheck if the old password is correct:'),
('26', 'DE', '-> OK.\r\n'),
('26', 'EN', '-> OK.'),
('27', 'DE', 'Setzen des neuen Kennwortes...:'),
('27', 'EN', 'Set your new password...:'),
('28', 'DE', 'Neues Kennwort wurde gesetzt.'),
('28', 'EN', 'New password saved.'),
('29', 'DE', 'Ein Fehler ist aufgetreten.\r\nProbiere es noch einmal.'),
('29', 'EN', 'An error has occured.\r\nPlease try again.'),
('30', 'DE', '-> nicht OK.\r\nBitte nocheinmal probieren.'),
('30', 'EN', '-> not OK.\r\nPlease try again.\r\n'),
('31', 'DE', 'Kennwörter sind nicht gleich. Bitte wiederholen.'),
('31', 'EN', 'The passwords don''t match. Please try again.'),
('32', 'DE', 'Neues Farblayout wurde gesetzt. Mit der nächsten Seite wird es aktiv.'),
('32', 'EN', 'New color settings are saved. On the next page it will be active.'),
('33', 'DE', 'Sprache wurde gesetzt. Mit der nächsten Seite wird es aktiv.'),
('33', 'EN', 'Language is saved. On the next page it will be active.'),
('34', 'DE', 'Avatar wurde gesetzt.'),
('34', 'EN', 'Avatar is saved.'),
('35', 'DE', '<b>Neue Anfrage:</b>\r\nIn diesem Formular hast du die Möglichkeit, den Dispatchern eine Frage zu stellen. Wenn diese beantwortet ist, wirst du hier darüber informiert. Sollte die Frage von allgemeinem Interesse sein, wird diese in die FAQ übernommen.'),
('35', 'EN', '<b>New Question</b>\r\nWith this form you may sumbit questions to our Dispatcher. Topics of common interest may be added to the FAQ. (Section: answered questions).\r\n'),
('36', 'DE', 'Stelle hier deine Frage'),
('36', 'EN', 'Tell us your question'),
('37', 'DE', 'Deine Anfrage war:'),
('37', 'EN', 'Your question was:'),
('38', 'DE', 'Diese liegt nun bei den Dispatchern zur Beantwortung vor.'),
('38', 'EN', 'It is queued for answering.'),
('39', 'DE', 'Deine bisherigen Anfragen:'),
('39', 'EN', 'Your past inquiries:'),
('40', 'DE', 'Offene Anfragen:'),
('40', 'EN', 'Open inquiries:'),
('41', 'DE', 'keine vorhanden...'),
('41', 'EN', 'nothing exists...'),
('42', 'DE', 'Beantwortete Anfragen:'),
('42', 'EN', 'Answered inquiries:'),
('pub_index_pass_no_ok', 'DE', 'Dein Passwort ist nicht korrekt. Bitte probiere es nocheinmal:'),
('pub_index_User_unset', 'DE', 'Es wurde kein User mit deinem Nick gefunden. Bitte probiere es noch einmal oder wende dich an die Dispatcher.'),
('pub_index_User_more_as_one', 'DE', 'Für deinen Nick gab es mehrere User... bitte wende dich an die Dispatcher'),
('Hello', 'DE', 'Hallo '),
('Hello', 'EN', 'Hello '),
('pub_schicht_beschreibung', 'DE', 'Hier kannst du dich für Schichten eintragen. Dazu such dir eine freie Schicht und klicke auf den Link! Du kannst dir eine Schicht über den Raum bzw. Datum aussuchen. Wähle hierfür einen Tag / ein Datum aus.'),
('pub_schicht_auswahl_raeume', 'DE', 'Zur Auswahl stehende Räume:'),
('pub_schicht_alles_1', 'DE', 'Und natürlich kannst du dir auch '),
('pub_schicht_alles_2', 'DE', 'alles '),
('pub_schicht_alles_3', 'DE', 'auf einmal anzeigen lassen.'),
('pub_schicht_Anzeige_1', 'DE', 'Anzeige des Schichtplans am '),
('pub_schicht_Anzeige_2', 'DE', ' im Raum: '),
('pub_schicht_Anzeige_3', 'DE', 'Anzeige des Schichtplans für den '),
('inc_schicht_engel', 'DE', 'Engel'),
('inc_schicht_engel', 'EN', 'Angel'),
('inc_schicht_ist', 'DE', 'ist'),
('inc_schicht_sind', 'DE', 'sind'),
('inc_schicht_weitere', 'DE', ' weitere'),
('inc_schicht_weiterer', 'DE', ' weiterer'),
('inc_schicht_werden', 'DE', ' werden '),
('inc_schicht_wird', 'DE', ' wird '),
('inc_schicht_noch_gesucht', 'DE', ' noch gesucht'),
('inc_schicht_und', 'DE', ' und '),
('pub_wake_beschreibung', 'DE', 'hier kannst du dich zum Wecken eintragen. Dazu sage einfach wann und wo und der Engel vom Dienst wird dich wecken.'),
('pub_wake_beschreibung2', 'DE', 'Alle eingetragenen Weckwünsche, die nächsten zuerst.'),
('pub_wake_Datum', 'DE', 'Datum'),
('pub_wake_Ort', 'DE', 'Ort'),
('pub_wake_Bemerkung', 'DE', 'Bermerkung'),
('lageplan_text1', 'DE', 'Hier eine &Uuml;bersicht &uuml;ber die Raumssituation:'),
('pub_wake_Text2', 'DE', 'Hier kannst du einen neuen Eintrag erfassen:'),
('pub_wake_bouton', 'DE', 'Weck mich!'),
('pub_wake_bouton', 'EN', 'wake me up!'),
('pub_wake_del', 'EN', 'delete'),
('pub_mywake_beschreibung1', 'DE', 'Hier siehst du die Schichten, für die du dich eingetragen hast.'),
('pub_mywake_beschreibung2', 'DE', 'Bitte versuche pünktlich zu den Schichten zu erscheinen.'),
('pub_mywake_beschreibung3', 'DE', 'Hier hast du auch die Möglichkeit, dich bis '),
('pub_mywake_beschreibung4', 'DE', ' Stunden vor Schichtbeginn auszutragen.'),
('pub_mywake_anzahl1', 'DE', 'Du hast dich für '),
('pub_mywake_anzahl2', 'DE', ' Schichten eingetragen'),
('pub_mywake_Datum', 'DE', 'Datum'),
('pub_mywake_Uhrzeit', 'DE', 'Uhrzeit'),
('pub_mywake_Ort', 'DE', 'Ort'),
('pub_mywake_Bemerkung', 'DE', 'Bemerkung'),
('pub_mywake_austragen', 'DE', 'austragen'),
('pub_mywake_delate1', 'DE', 'Schicht wird ausgetragen...'),
('pub_mywake_add_ok', 'DE', 'Schicht wurde ausgetragen.'),
('pub_mywake_add_ko', 'DE', 'Sorry, ein kleiner Fehler ist aufgetreten... probiere es doch bitte nocheinmal :)'),
('pub_mywake_after', 'DE', 'zu spät'),
('pub_index_pass_no_ok', 'EN', 'Your password is incorrect.  Please try it again:\r\n'),
('pub_index_User_unset', 'EN', 'No user was found with that Nickname.  Please try again.  If you are still having problems, ask an Dispatcher\r\n'),
('pub_index_User_more_as_one', 'EN', 'This nickname is registered for more than one user, please contact an Dispatcher.\r\n'),
('pub_schicht_beschreibung', 'EN', 'Here, you can register for shifts.  To do this, please choose an empty shift, and click the link.  You can choose the place, time and date of the shift. You can choose the date at the right.\r\n'),
('pub_schicht_alles_1', 'EN', 'And of course you can also choose to show\r\n'),
('pub_schicht_alles_2', 'EN', 'everything'),
('pub_schicht_alles_3', 'EN', ' at once.'),
('pub_schicht_auswahl_raeume', 'EN', 'To the selection of available areas.\r\n'),
('pub_schicht_Anzeige_1', 'EN', 'Show the shift schedule\r\n'),
('pub_schicht_Anzeige_2', 'EN', ' in Area: '),
('pub_schicht_Anzeige_3', 'EN', 'Show the shift schedule for\r\n'),
('inc_schicht_ist', 'EN', 'is'),
('inc_schicht_sind', 'EN', 'are '),
('pub_wake_beschreibung', 'EN', 'Here you can register for a wake-up "call".  Simply say when and where the angel should come to wake you.\r\n'),
('inc_schicht_weitere', 'EN', ' more'),
('inc_schicht_weiterer', 'EN', ' more'),
('inc_schicht_werden', 'EN', ' are '),
('inc_schicht_wird', 'EN', ' is  '),
('inc_schicht_noch_gesucht', 'EN', ' still needed '),
('inc_schicht_und', 'EN', ' and '),
('pub_wake_beschreibung2', 'EN', 'All ordered wake-up calls, next first.'),
('pub_wake_Datum', 'EN', 'Date'),
('pub_wake_Ort', 'EN', 'Place'),
('pub_wake_change', 'EN', 'delete'),
('pub_wake_Bemerkung', 'EN', 'Notes'),
('pub_wake_change', 'DE', 'löschen'),
('pub_wake_del', 'DE', 'löschen'),
('pub_wake_Text2', 'EN', 'Schedule a new wake-up here:'),
('pub_mywake_beschreibung1', 'EN', 'Here are the shifts that you have signed up for.\r\n'),
('pub_mywake_beschreibung2', 'EN', 'Please try to arrive for your shift on time.  Be punctual!\r\n'),
('pub_mywake_beschreibung3', 'EN', 'Here you can remove yourself from a shift up to\r\n'),
('pub_mywake_beschreibung4', 'EN', ' hours before your shift is scheduled to begin.'),
('pub_mywake_anzahl1', 'EN', 'You have signed up for '),
('pub_mywake_anzahl2', 'EN', ' shift(s) so far'),
('pub_mywake_Datum', 'EN', 'Date'),
('pub_mywake_Uhrzeit', 'EN', 'Time'),
('pub_mywake_Ort', 'EN', 'Place'),
('pub_mywake_Bemerkung', 'EN', 'Notes'),
('pub_schichtplan_add_Error', 'EN', 'An error occurred'),
('pub_mywake_austragen', 'EN', 'remove'),
('pub_mywake_austragen_n_c', 'EN', 'is no longer possible'),
('pub_mywake_austragen_n_c', 'DE', 'nicht mehr möglich'),
('pub_mywake_delate1', 'EN', 'Shift is being removed...'),
('pub_mywake_add_ok', 'EN', 'Shift has been removed.'),
('pub_mywake_add_ko', 'EN', 'Sorry, something went wrong somewhere.  Please try it again. :)\r\n'),
('pub_mywake_after', 'EN', 'sorry, too late!'),
('index_text1', 'DE', 'Widerstand ist zwecklos!'),
('index_text2', 'DE', 'Deine physikalischen und biologischen Eigenschaften werden den unsrigen hinzugefuegt!'),
('index_text1', 'EN', 'Resistance is futile!\r\n'),
('index_text3', 'DE', 'Datenerfassungsbogen:'),
('index_text2', 'EN', 'Your biological and physical parameters will be added to our collectiv!'),
('index_text4', 'EN', 'Please note: You have to activate cookies!'),
('index_text4', 'DE', 'Achtung: Cookies müssen aktiviert sein'),
('index_text3', 'EN', 'Assimilating angel:'),
('index_lang_nick', 'DE', 'Wie ist Dein Nick:'),
('index_lang_pass', 'DE', 'Wie ist Dein Passwort:'),
('index_lang_send', 'DE', 'Fullfill order!'),
('index_lang_nick', 'EN', 'What is your Loginname:\r\n'),
('index_lang_pass', 'EN', 'What is your password:'),
('index_logout', 'DE', 'Du wurdest erfolgreich abgemeldet.'),
('index_logout', 'EN', 'You have been successfully logged out.'),
('menu_index', 'DE', 'Index'),
('menu_FAQ', 'DE', 'FAQ'),
('menu_plan', 'DE', 'Lageplan'),
('menu_index', 'EN', 'Index'),
('menu_FAQ', 'EN', 'FAQ'),
('pub_menu_menuname', 'DE', 'Menü'),
('menu_plan', 'EN', 'Map'),
('news', 'EN', 'News'),
('news', 'DE', 'News'),
('pub_menu_Engelbesprechung', 'DE', 'Engelbesprechung'),
('pub_menu_menuname', 'EN', 'Menu'),
('pub_menu_Schichtplan', 'DE', 'Schichtplan'),
('pub_menu_Wecken', 'DE', 'Wecken'),
('pub_menu_mySchichtplan', 'DE', 'Mein Schichtplan'),
('pub_menu_questionEngel', 'DE', 'Anfragen an die Dispatcher'),
('user_settings', 'DE', 'Einstellungen'),
('pub_menu_Engelbesprechung', 'EN', 'Angel meeting'),
('logout', 'DE', 'Abmelden'),
('pub_menu_Schichtplan', 'EN', 'Available Shifts'),
('pub_menu_Wecken', 'EN', 'Wake-up Service'),
('index_lang_send', 'EN', 'Fullfill order!'),
('pub_menu_mySchichtplan', 'EN', 'My Shifts'),
('pub_menu_questionEngel', 'EN', 'Questions for the Dispatcher'),
('logout', 'EN', 'Logout'),
('user_settings', 'EN', 'Settings'),
('menu_Name', 'DE', 'Garage'),
('menu_Name', 'EN', 'Garage'),
('menu_MakeUser', 'DE', 'Benutzer anlegen'),
('menu_MakeUser', 'EN', 'Create new account'),
('pub_menu_Waeckerlist', 'DE', 'Weckerlist'),
('pub_menu_Waeckerlist', 'EN', 'Wake-up list'),
('pub_waeckliste_Text1', 'DE', 'dies ist die Weckliste. Schau hier bitte, wann die Leute geweckt werden wollen und erledige dies... schliesslich willst du bestimmt nicht deren Schichten uebernehmen :-)\r\n<br><br>\r\nDie bisherigen eingetragenen Zeiten:'),
('pub_waeckliste_Nick', 'DE', 'Nick'),
('pub_waeckliste_Nick', 'EN', 'Nick'),
('pub_waeckliste_Datum', 'DE', 'Datum'),
('pub_waeckliste_Datum', 'EN', 'Date'),
('pub_waeckliste_Ort', 'DE', 'Ort'),
('pub_waeckliste_Ort', 'EN', 'Place'),
('pub_waeckliste_Comment', 'DE', 'Bemerkung'),
('pub_waeckliste_Comment', 'EN', 'comment'),
('pub_waeckliste_Text1', 'EN', 'This is the wake-up list. Pleace look here, when the angels  want to wake-up and \r\nhandle this... you don''t want to take on this shift, isn''t it?:-)\r\n<br><br>\r\nShow all entries:'),
('pub_schichtplan_add_ToManyYousers', 'DE', 'FEHLER: Es wurden keine weiteren Engel benötigt !!'),
('pub_schichtplan_add_ToManyYousers', 'EN', 'ERROR: There are enough angels for this shift'),
('pub_mywake_Len', 'DE', 'Länge'),
('pub_mywake_Len', 'EN', 'length'),
('pub_schichtplan_add_AllreadyinShift', 'DE', 'du bist bereits in einer Schicht eingetragen!'),
('pub_schichtplan_add_AllreadyinShift', 'EN', 'you have another shift on this time'),
('pub_schichtplan_add_Error', 'DE', 'Ein Fehler ist aufgetreten'),
('pub_schichtplan_add_WriteOK', 'DE', 'Du bist jetzt der Schicht zugeteilt. Vielen Dank für deine Mitarbeit.'),
('pub_schichtplan_add_Text1', 'DE', 'Hier kannst du dich in eine Schicht eintragen. Als Kommentar kannst du etwas x-beliebiges eintragen, wie z. B.\r\nwelcher Vortrag dies ist oder Ähnliches. Den Kommentar kannst nur du sehen. '),
('pub_schichtplan_add_Date', 'DE', 'Datum'),
('pub_schichtplan_add_Place', 'DE', 'Ort'),
('pub_schichtplan_add_Job', 'DE', 'Aufgabe'),
('pub_schichtplan_add_Len', 'DE', 'Dauer'),
('pub_schichtplan_add_TextFor', 'DE', 'Text zur Schicht'),
('pub_schichtplan_add_Comment', 'DE', 'Dein Kommentar'),
('pub_schichtplan_add_submit', 'DE', 'Ja, ich will helfen...&quot;'),
('index_text5', 'DE', 'Bitte überprüfen Sie den SSL Key'),
('index_text5', 'EN', 'Please check your SSL-Key:'),
('pub_myshift_Edit_Text1', 'DE', 'Hier könnt ihr euren Kommentar ändern:'),
('pub_myshift_EditSave_Text1', 'DE', 'Text wird gespeichert'),
('pub_myshift_EditSave_OK', 'DE', 'erfolgreich gespeichert.'),
('pub_myshift_EditSave_KO', 'DE', 'Fehler beim Speichern'),
('pub_sprache_text1', 'DE', 'hier kannst du die übersetzten Texte bearbeiten.'),
('pub_sprache_text1', 'EN', 'here can you edit the texts of the engelsystem'),
('pub_sprache_TextID', 'EN', 'TextID'),
('pub_sprache_TextID', 'DE', 'TextID'),
('pub_sprache_Sprache', 'DE', 'Sprache '),
('pub_sprache_Sprache', 'EN', 'Language '),
('pub_schichtplan_add_Place', 'EN', 'place'),
('pub_sprache_Edit', 'DE', 'Bearbeiten'),
('pub_sprache_Edit', 'EN', 'edit'),
('pub_schichtplan_add_Date', 'EN', 'Date'),
('pub_myshift_EditSave_KO', 'EN', 'Error on saving'),
('pub_myshift_EditSave_OK', 'EN', 'save OK'),
('pub_myshift_EditSave_Text1', 'EN', 'Text was saved'),
('pub_myshift_Edit_Text1', 'EN', 'Here can you change your comment:'),
('pub_schichtplan_add_Comment', 'EN', 'Your comment'),
('pub_aktive_Text1', 'DE', 'Diese Funktion ermöglicht es den Dispatchern, schnell einen Engel mit einer vorgebbaren Anzahl an Stunden als Aktiv zu markieren.'),
('pub_aktive_Text1', 'EN', 'This function enables the archangels to mark angels as active who worked enough hours.'),
('pub_aktive_Text2', 'DE', 'Über die Engelliste kann dies für einzelne Engel erledigt werden.'),
('pub_aktive_Text2', 'EN', 'Over the angellist you can do this for single angels.'),
('pub_aktive_Text31', 'DE', 'Alle Engel mit mindestens'),
('pub_aktive_Text31', 'EN', 'All angels with at least'),
('pub_aktive_Text32', 'DE', 'Schichten als Aktiv markieren'),
('pub_aktive_Text32', 'EN', 'mark shifts as &quot;active&quot;'),
('pub_aktive_Nick', 'DE', 'Nick'),
('pub_aktive_Nick', 'EN', 'Nick'),
('pub_aktive_Anzahl', 'DE', 'Anzahl Schichten'),
('pub_aktive_Anzahl', 'EN', 'number of shifts'),
('pub_aktive_Time', 'DE', 'Gesamtzeit'),
('pub_aktive_Time', 'EN', 'summary time'),
('pub_schichtplan_add_submit', 'EN', 'Yes, I want to help...&quot;'),
('pub_schichtplan_add_Len', 'EN', 'duration'),
('pub_schichtplan_add_Job', 'EN', 'job'),
('pub_aktive_Text5_1', 'DE', 'Alle Engel mit mindestens '),
('pub_aktive_Text5_1', 'EN', 'All angels with at least '),
('pub_aktive_Text5_2', 'DE', ' Schichten werden jetzt als &quot;Aktiv&quot; markiert'),
('pub_aktive_Text5_2', 'EN', ' shifts were marked as &quot;active&quot;'),
('pub_aktive_Active', 'DE', 'Aktiv'),
('pub_aktive_Active', 'EN', 'active'),
('pub_schichtplan_add_TextFor', 'EN', 'text for shift'),
('pub_schichtplan_add_WriteOK', 'EN', 'Now, you signed up for this shift. Thank you for your cooperation.'),
('pub_schichtplan_add_Text1', 'EN', 'Here you can sign up for a shift. As commend can you write what you want, it is only for you.'),
('pub_schichtplan_colision', 'DE', '<h1>Fehler</h1>\r\nÜberschneidung von Schichten:'),
('pub_schichtplan_colision', 'EN', '<h1>error</h1>\r\noverlap on shift:'),
('pub_schicht_EmptyShifts', 'DE', 'Die nächsten 15 freien Schichten:'),
('pub_schicht_EmptyShifts', 'EN', 'The next 15 empty shifts:'),
('inc_schicht_date', 'DE', 'Datum'),
('inc_schicht_date', 'EN', 'Date'),
('inc_schicht_time', 'DE', 'Zeit'),
('inc_schicht_time', 'EN', 'Time'),
('inc_schicht_room', 'DE', 'Raum'),
('inc_schicht_room', 'EN', 'room'),
('inc_schicht_commend', 'DE', 'Kommentar'),
('inc_schicht_commend', 'EN', 'comment'),
('pub_einstellungen_Name', 'DE', 'Nachname:'),
('pub_einstellungen_Name', 'EN', 'Last name:'),
('pub_einstellungen_Nick', 'DE', 'Nick:'),
('pub_einstellungen_Nick', 'EN', 'nick:'),
('pub_einstellungen_Vorname', 'DE', 'Vorname:'),
('pub_einstellungen_Vorname', 'EN', 'first name:'),
('pub_einstellungen_Alter', 'DE', 'Alter:'),
('pub_einstellungen_Alter', 'EN', 'Age:'),
('pub_einstellungen_Telefon', 'DE', 'Telefon:'),
('pub_einstellungen_Telefon', 'EN', 'Phone:'),
('pub_einstellungen_Handy', 'DE', 'Handy:'),
('pub_einstellungen_Handy', 'EN', 'Mobile Phone:'),
('pub_einstellungen_DECT', 'DE', 'DECT:'),
('pub_einstellungen_DECT', 'EN', 'DECT:'),
('pub_einstellungen_email', 'DE', 'E-Mail:'),
('pub_einstellungen_email', 'EN', 'email:'),
('pub_einstellungen_Text_UserData', 'EN', 'Here you can change your user details.'),
('pub_einstellungen_UserDateSaved', 'DE', 'Deine Beschreibung für unsere Engelverwaltung wurde geändert.'),
('pub_einstellungen_UserDateSaved', 'EN', 'Your user details were saved.'),
('pub_menu_SchichtplanBeamer', 'DE', 'Schichtplan für Beamer optimiert'),
('pub_menu_SchichtplanBeamer', 'EN', 'Shifts for beamer optimice'),
('pub_einstellungen_Text_UserData', 'DE', 'Hier kannst du deine Beschreibung für unsere Engelverwaltung ändern.'),
('lageplan_text1', 'EN', 'This is a map of available rooms:'),
('register', 'DE', 'Engel werden'),
('register', 'EN', 'Become an angel'),
('makeuser_text1', 'DE', 'Mit dieser Maske meldet ihr euch im Engelsystem an. Durch das Engelsystem findet auf der Veranstaltung die Aufgabenverteilung der Engel statt.\r\n\r\n'),
('makeuser_text1', 'EN', 'By completing this form you''re registering as a Chaos-Angel. This script will create you an account in the angel task sheduler.\r\n\r\n'),
('makeuser_Nickname', 'DE', 'Nickname'),
('makeuser_Nickname', 'EN', 'nick'),
('makeuser_text2', 'DE', 'Habt ihr schon einmal bei einer<br />\r\nCCC-Veranstaltung mitgeholfen? <br />\r\nWenn ja, in welchem <br />\r\nwelchen Aufgabengebiet(en)?'),
('makeuser_text2', 'EN', 'Did you help at former <br />\r\nCCC events and which tasks <br />\r\nhave you performed then?'),
('makeuser_Nachname', 'DE', 'Nachname'),
('makeuser_Nachname', 'EN', 'last name'),
('makeuser_Vorname', 'DE', 'Vorname'),
('makeuser_Vorname', 'EN', 'first name'),
('makeuser_Alter', 'DE', 'Alter'),
('makeuser_Alter', 'EN', 'age'),
('makeuser_Telefon', 'DE', 'Telefon'),
('makeuser_Telefon', 'EN', 'phone'),
('makeuser_DECT', 'DE', 'DECT'),
('makeuser_DECT', 'EN', 'DECT'),
('makeuser_Handy', 'DE', 'Handy'),
('makeuser_Handy', 'EN', 'mobile'),
('makeuser_E-Mail', 'DE', 'E-Mail'),
('makeuser_E-Mail', 'EN', 'e-mail'),
('makeuser_T-Shirt', 'DE', 'T-Shirt Größe'),
('makeuser_T-Shirt', 'EN', 'shirt size'),
('makeuser_Engelart', 'DE', 'Zuteilung'),
('makeuser_Engelart', 'EN', 'designation'),
('makeuser_Passwort', 'DE', 'Passwort'),
('makeuser_Passwort', 'EN', 'password'),
('makeuser_Passwort2', 'DE', 'Passwort Bestätigung'),
('makeuser_Passwort2', 'EN', 'password confirm'),
('makeuser_Anmelden', 'DE', 'Anmelden...'),
('makeuser_Anmelden', 'EN', 'register me...'),
('makeuser_text3', 'DE', '*Dieser Eintrag ist eine Pflichtangabe.'),
('makeuser_text3', 'EN', '* entry required!'),
('makeuser_error_nick1', 'DE', 'Fehler: Nickname &quot;'),
('makeuser_error_nick1', 'EN', 'error: your nick &quot;'),
('makeuser_error_nick2', 'DE', '&quot; ist zu kurz gew&auml;hlt (Mindestens 2 Zeichen).'),
('makeuser_error_nick2', 'EN', '&quot; is too short (min. 2 characters)'),
('makeuser_error_mail', 'DE', 'Fehler: E-Mail-Adresse ist nicht g&uuml;ltig.'),
('makeuser_error_mail', 'EN', 'error: e-mail address is not correct'),
('makeuser_error_password1', 'DE', 'Fehler: Passwörter sind nicht identisch.'),
('makeuser_error_password1', 'EN', 'error: your passwords don''t match'),
('makeuser_error_password2', 'DE', 'Fehler: Passwort ist zu kurz (Mindestens 6 Zeichen)'),
('makeuser_error_password2', 'EN', 'error: your password is to short (at least 6 characters)'),
('makeuser_error_write1', 'DE', 'Fehler: Kann die eingegebenen Daten nicht sichern?!?'),
('makeuser_error_write1', 'EN', 'error: can t save your data...'),
('makeuser_writeOK', 'DE', 'Registration erfolgreich.'),
('makeuser_writeOK', 'EN', 'transmitted.'),
('makeuser_error_write2', 'DE', 'Fehler: Beim Speichern der Userrechte...'),
('makeuser_error_write2', 'EN', 'error: can&#039;t save userrights... '),
('makeuser_writeOK2', 'DE', 'Userrechte wurden gespeichert...'),
('makeuser_writeOK2', 'EN', 'userright was saved...'),
('makeuser_writeOK3', 'EN', 'Your account was successfully created, have a lot of fun.'),
('makeuser_writeOK3', 'DE', 'Dein Account wurde erfolgreich gespeichert, have a lot of fun.'),
('makeuser_writeOK4', 'DE', 'Engel Registriert!'),
('makeuser_writeOK4', 'EN', 'Angel registered!'),
('makeuser_text4', 'DE', 'Wenn du dich als Engel registrieren  möchtest, fülle bitte folgendes Formular aus:'),
('makeuser_text4', 'EN', 'If you would like to be a chaos angel please insert following details into this form:'),
('makeuser_error_nick3', 'DE', '&quot; existiert bereits.'),
('makeuser_error_nick3', 'EN', '&quot; already exist.'),
('makeuser_Hometown', 'EN', 'hometown'),
('makeuser_Hometown', 'DE', 'Wohnort'),
('pub_einstellungen_Hometown', 'DE', 'Wohnort'),
('pub_einstellungen_Hometown', 'EN', 'hometown'),
('makeuser_error_Alter', 'DE', 'Fehler: Dein Alter muss eine Zahl oder leer sein'),
('makeuser_error_Alter', 'EN', 'error: your age must be a number or empty'),
('user_messages', 'DE', 'Nachrichten'),
('user_messages', 'EN', 'Messages'),
('pub_messages_Datum', 'DE', 'Datum'),
('pub_messages_Datum', 'EN', 'date'),
('pub_messages_Von', 'DE', 'Gesendet'),
('pub_messages_Von', 'EN', 'transmitted'),
('pub_messages_An', 'DE', 'Empfänger'),
('pub_messages_An', 'EN', 'recipient'),
('pub_messages_Text', 'DE', 'Text'),
('pub_messages_Text', 'EN', 'text'),
('pub_messages_Send1', 'DE', 'Nachricht wird gesendet'),
('pub_messages_Send1', 'EN', 'message will be send'),
('pub_messages_Send_OK', 'DE', 'Senden erfolgeich'),
('pub_messages_Send_OK', 'EN', 'transmitting was OK'),
('pub_messages_Send_Error', 'DE', 'Senden ist fehlgeschlagen'),
('pub_messages_Send_Error', 'EN', 'transmitting was terminate with an Error'),
('pub_messages_MarkRead', 'DE', 'als gelesen makieren'),
('pub_messages_MarkRead', 'EN', 'mark as read'),
('pub_messages_NoCommand', 'DE', 'kein Kommando erkannt'),
('pub_messages_NoCommand', 'EN', 'no command recognised'),
('pub_messages_MarkRead_OK', 'DE', 'als gelesen markiert'),
('pub_messages_MarkRead_OK', 'EN', 'mark as read'),
('pub_messages_MarkRead_KO', 'DE', 'Fehler beim als gelesen Markieren'),
('pub_messages_MarkRead_KO', 'EN', 'error on: mark as read'),
('pub_messages_text1', 'DE', 'hier kannst du Nachrichten an andere Engel versenden'),
('pub_messages_text1', 'EN', 'here can you leave messages for other angels'),
('pub_messages_DelMsg', 'DE', 'Nachricht löschen'),
('pub_messages_DelMsg', 'EN', 'delete message'),
('pub_messages_DelMsg_OK', 'DE', 'Nachricht gelöscht'),
('pub_messages_DelMsg_OK', 'EN', 'delete message'),
('pub_messages_DelMsg_KO', 'DE', 'Nachricht konnte nicht gelöscht werden'),
('pub_messages_DelMsg_KO', 'EN', 'cannot delete message'),
('pub_messages_new1', 'DE', 'Du hast'),
('pub_messages_new1', 'EN', 'You have'),
('pub_messages_new2', 'DE', 'neue Nachrichten'),
('pub_messages_new2', 'EN', 'new messages'),
('pub_messages_NotRead', 'DE', 'nicht gelesen'),
('pub_messages_NotRead', 'EN', 'not read'),
('pub_mywake_Name', 'DE', 'Schicht Titel'),
('pub_mywake_Name', 'EN', 'shift title'),
('pub_sprache_ShowEntry', 'DE', 'Einträge anzeigen'),
('pub_sprache_ShowEntry', 'EN', 'show entrys'),
('admin_rooms', 'DE', 'Räume'),
('admin_rooms', 'EN', 'Rooms'),
('admin_angel_types', 'DE', 'Engeltypen'),
('admin_angel_types', 'EN', 'Angel types'),
('pub_menu_SchichtplanEdit', 'DE', 'Schichtplan'),
('pub_menu_SchichtplanEdit', 'EN', 'Shiftplan'),
('pub_menu_UpdateDB', 'DE', 'UpdateDB'),
('pub_menu_UpdateDB', 'EN', 'UpdateDB'),
('pub_menu_Dect', 'DE', 'Dect'),
('pub_menu_Dect', 'EN', 'Dect'),
('pub_menu_Engelliste', 'DE', 'Engelliste'),
('pub_menu_Engelliste', 'EN', 'Angel-list'),
('pub_menu_EngelDefaultSetting', 'DE', 'Engel Voreinstellungen'),
('pub_menu_EngelDefaultSetting', 'EN', 'Angel default setting'),
('pub_menu_Aktivliste', 'DE', 'Aktiv Liste'),
('pub_menu_Aktivliste', 'EN', 'active list'),
('pub_menu_T-Shirtausgabe', 'DE', 'T-Shirtausgabe'),
('pub_menu_T-Shirtausgabe', 'EN', 'T-Shirt handout'),
('pub_menu_News-Verwaltung', 'DE', 'News-Verwaltung'),
('pub_menu_News-Verwaltung', 'EN', 'News-Center'),
('faq', 'DE', 'FAQ'),
('faq', 'EN', 'FAQ'),
('pub_menu_FreeEngel', 'DE', 'Freie Engel'),
('pub_menu_FreeEngel', 'EN', 'free angels'),
('pub_menu_Debug', 'DE', 'Debug'),
('pub_menu_Debug', 'EN', 'Debug'),
('pub_menu_Recentchanges', 'DE', 'Letzte Änderungen'),
('pub_menu_Recentchanges', 'EN', 'recent changes'),
('pub_menu_Language', 'DE', 'Sprachen'),
('pub_menu_Language', 'EN', 'Language'),
('makeuser_text0', 'DE', 'Anmeldung als Engel'),
('makeuser_text0', 'EN', 'Angel registration'),
('/', 'DE', 'Willkommen'),
('/', 'EN', 'welcome'),
('nonpublic/', 'DE', 'Garage'),
('nonpublic/', 'EN', 'garage'),
('admin/', 'DE', 'admin'),
('admin/', 'EN', 'admin'),
('index.php', 'DE', 'Start'),
('index.php', 'EN', 'Start'),
('logout.php', 'DE', 'logout'),
('logout.php', 'EN', 'logout'),
('faq.php', 'DE', 'FAQ'),
('faq.php', 'EN', 'FAQ'),
('lageplan.php', 'DE', 'Lageplan'),
('lageplan.php', 'EN', 'Map'),
('nonpublic/index.php', 'DE', ' '),
('nonpublic/index.php', 'EN', ' '),
('nonpublic/news.php', 'EN', 'News'),
('nonpublic/news.php', 'DE', 'News'),
('nonpublic/news_comments.php', 'EN', ' '),
('nonpublic/news_comments.php', 'DE', ' '),
('nonpublic/engelbesprechung.php', 'DE', 'Engelbesprechung'),
('nonpublic/engelbesprechung.php', 'EN', 'Angel gathering'),
('nonpublic/schichtplan.php', 'DE', 'Schichtplan'),
('nonpublic/schichtplan.php', 'EN', 'Available Shifts'),
('nonpublic/schichtplan_add.php', 'DE', ' '),
('nonpublic/schichtplan_add.php', 'EN', ' '),
('nonpublic/myschichtplan.php', 'DE', 'Mein Schichtplan'),
('nonpublic/myschichtplan.php', 'EN', 'My Shifts'),
('nonpublic/myschichtplan_ical.php', 'DE', ' '),
('nonpublic/myschichtplan_ical.php', 'EN', ' '),
('nonpublic/einstellungen.php', 'DE', 'Einstellungen'),
('nonpublic/einstellungen.php', 'EN', 'Options'),
('nonpublic/wecken.php', 'DE', 'Wecken'),
('nonpublic/wecken.php', 'EN', 'Wake-up Service'),
('nonpublic/waeckliste.php', 'DE', 'Weckerlist'),
('nonpublic/waeckliste.php', 'EN', 'Wake-up list'),
('nonpublic/messages.php', 'DE', 'Nachrichten'),
('nonpublic/messages.php', 'EN', 'messages'),
('nonpublic/schichtplan_beamer.php', 'DE', 'Schichtplan für Beamer optimiert'),
('nonpublic/schichtplan_beamer.php', 'EN', 'Shifts for beamer optimice'),
('nonpublic/faq.php', 'DE', 'Anfragen an die Dispatcher'),
('nonpublic/faq.php', 'EN', 'Questions for the Dispatcher'),
('admin/index.php', 'DE', ' '),
('admin/index.php', 'EN', ' '),
('pub_einstellungen_PictureUpload', 'DE', 'Hochzuladendes Bild auswählen:'),
('pub_einstellungen_PictureUpload', 'EN', 'Choose a picture to Upload:'),
('pub_einstellungen_send_OK', 'EN', 'The file was uploaded successfully'),
('pub_einstellungen_send_OK', 'DE', 'Die Datei wurde erfolgreich hochgeladen.'),
('pub_einstellungen_PictureNoShow', 'EN', 'The photo isnot free at the moment'),
('pub_einstellungen_PictureShow', 'DE', 'Das Foto ist freigegeben'),
('pub_einstellungen_PictureShow', 'EN', 'The photo is free at the moment'),
('pub_einstellungen_del_OK', 'DE', 'Bild wurde erfolgreich gel?scht.'),
('pub_einstellungen_del_OK', 'EN', 'Picture was deleted successfully.'),
('pub_einstellungen_del_KO', 'DE', 'Bild wurde nicht erfolgreich gel?scht.'),
('pub_einstellungen_del_KO', 'EN', 'Picture was not deleted successfully.'),
('delete', 'DE', 'löschen'),
('delete', 'EN', 'delete'),
('upload', 'EN', 'upload'),
('upload', 'DE', 'hochladen'),
('pub_einstellungen_PictureNoShow', 'DE', 'Das Foto ist nicht freigegeben'),
('pub_einstellungen_send_KO', 'DE', 'Beim Hochladen ist ein Fehler aufgetreten.'),
('pub_einstellungen_send_KO', 'EN', 'An error was detected. Please try again!'),
('admin/room.php', 'DE', 'Räume'),
('admin/room.php', 'EN', 'rooms'),
('admin/EngelType.php', 'DE', 'Engeltypen'),
('admin/EngelType.php', 'EN', 'Angel-Types'),
('admin/schichtplan.php', 'DE', 'Schichtplan'),
('admin/schichtplan.php', 'EN', 'Shiftplan'),
('admin/shiftadd.php', 'DE', ' '),
('admin/shiftadd.php', 'EN', ' '),
('admin/schichtplan_druck.php', 'DE', ' '),
('admin/schichtplan_druck.php', 'EN', ' '),
('admin/dbUpdateFromXLS.php', 'DE', 'UpdateDB'),
('admin/dbUpdateFromXLS.php', 'EN', 'UpdateDB'),
('admin/dect.php', 'DE', 'Dect'),
('admin/dect.php', 'EN', 'Dect'),
('admin/dect_call.php', 'DE', ' '),
('admin/dect_call.php', 'EN', ' '),
('admin_user', 'DE', 'Engelliste'),
('admin_user', 'EN', 'Manage angels'),
('admin/userDefaultSetting.php', 'DE', 'Engel Voreinstellungen'),
('admin/userDefaultSetting.php', 'EN', 'Angel default setting'),
('admin/UserPicture.php', 'DE', 'Benutzerbilder'),
('admin/UserPicture.php', 'EN', 'User Pictures'),
('admin/aktiv.php', 'DE', 'Aktiv Liste'),
('admin/aktiv.php', 'EN', 'active list'),
('admin/tshirt.php', 'DE', 'T-Shirtausgabe'),
('admin/tshirt.php', 'EN', 'T-Shirt handout'),
('admin/news.php', 'DE', 'News-Verwaltung'),
('admin/news.php', 'EN', 'News-Center'),
('admin/free.php', 'DE', 'Freie Engel'),
('admin/free.php', 'EN', 'free Angels'),
('admin/debug.php', 'DE', 'Debug'),
('admin/debug.php', 'EN', 'Debug'),
('admin/Recentchanges.php', 'DE', 'Letzte Änderungen'),
('admin/Recentchanges.php', 'EN', 'recentchanges'),
('admin/sprache.php', 'DE', 'Sprachen'),
('admin/sprache.php', 'EN', 'Language'),
('admin/faq.php', 'DE', 'FAQ'),
('admin/faq.php', 'EN', 'FAQ'),
('pub_myschichtplan_ical', 'DE', 'export my Shifts as iCal file'),
('pub_myschichtplan_ical', 'EN', 'iCal File exportieren'),
('Sprache', 'DE', 'Sprache'),
('Sprache', 'EN', 'Language'),
('start', 'DE', 'Start'),
('start', 'EN', 'Start'),
('login', 'DE', 'Login'),
('login', 'EN', 'Login'),
('credits', 'DE', 'Credits'),
('credits', 'EN', 'Credits'),
('pub_messages_Neu', 'DE', 'Neu'),
('pub_messages_Neu', 'EN', 'New'),
('admin_groups', 'DE', 'Gruppenrechte'),
('admin_groups', 'EN', 'Grouprights'),
('user_questions', 'DE', 'Erzengel fragen'),
('user_questions', 'EN', 'Ask arch angel'),
('admin_questions', 'DE', 'Fragen beantworten'),
('admin_questions', 'EN', 'Answer questions'),
('admin_faq', 'DE', 'FAQs bearbeiten'),
('admin_faq', 'EN', 'Edit FAQs'),
('news_comments', 'DE', 'News Kommentare'),
('news_comments', 'EN', 'News comments'),
('admin_news', 'DE', 'News verwalten'),
('admin_news', 'EN', 'Manage news'),
('user_meetings', 'DE', 'Treffen'),
('user_meetings', 'EN', 'Meetings'),
('admin_language', 'DE', 'Übersetzung'),
('admin_language', 'EN', 'Translation'),
('admin_log', 'EN', 'Log'),
('admin_log', 'DE', 'Log'),
('user_wakeup', 'DE', 'Weckservice'),
('user_wakeup', 'EN', 'Wakeup service'),
('admin_import', 'DE', 'Pentabarf Import'),
('admin_import', 'EN', 'Pentabarf import'),
('user_shifts', 'DE', 'Schichtplan'),
('user_shifts', 'EN', 'Shifts'),
('user_myshifts', 'DE', 'Meine Schichten'),
('user_myshifts', 'EN', 'My shifts'),
('admin_arrive', 'DE', 'Engel Ankunft'),
('admin_arrive', 'EN', 'Arrived angels'),
('admin_shifts', 'DE', 'Schichten erstellen'),
('admin_shifts', 'EN', 'Create shifts'),
('admin_active', 'DE', 'Engel Aktiv/T-Shirt'),
('admin_active', 'EN', 'Angel active/t-shirt'),
('admin_free', 'DE', 'Freie Engel'),
('admin_free', 'EN', 'Free angels'),
('admin_user_angeltypes', 'DE', 'Engeltypen freischalten'),
('admin_user_angeltypes', 'EN', 'Confirm angeltypes'),
('no_access_title', 'DE', 'Kein Zugriff'),
('no_access_text', 'DE', 'Du hast keinen Zugriff auf diese Seite. Vermutlich muss du dich erst anmelden/registrieren!'),
('no_access_title', 'EN', 'No Access'),
('no_access_text', 'EN', 'You don''t have permission to view this page. You probably have to sign in or register in order to gain access!'),
('rooms', 'DE', 'Räume'),
('rooms', 'EN', 'rooms'),
('days', 'DE', 'Tage'),
('days', 'EN', 'days'),
('tasks', 'DE', 'Aufgaben'),
('tasks', 'EN', 'tasks'),
('occupancy', 'DE', 'Belegung'),
('occupancy', 'EN', 'occupancy'),
('all', 'DE', 'alle'),
('all', 'EN', 'all'),
('none', 'DE', 'keine'),
('none', 'EN', 'none'),
('entries', 'DE', 'Einträge'),
('entries', 'EN', 'entries'),
('time', 'DE', 'Zeit'),
('time', 'EN', 'time'),
('room', 'DE', 'Raum'),
('room', 'EN', 'room'),
('to_filter', 'DE', 'filtern'),
('to_filter', 'EN', 'filter'),
('pub_schichtplan_tasks_notice', 'DE', 'Die hier angezeigten Aufgaben werden durch die Präferenzen in deinen Einstellungen beeinflusst! <a href="https://events.ccc.de/congress/2012/wiki/Volunteers#What_kind_of_volunteers_are_needed.3F">Beschreibung der einzelnen Aufgaben</a>.'),
('pub_schichtplan_tasks_notice', 'EN', 'The tasks shown here are influenced by the preferences you defined in your settings! <a href="https://events.ccc.de/congress/2012/wiki/Volunteers#What_kind_of_volunteers_are_needed.3F">Description of the jobs</a>.'),
('inc_schicht_ical_text', 'DE', 'Export der angezeigten Schichten. Im <a href="%s">iCal Format</a> oder im <a href="%s">JSON Format</a> (bitte geheimhalten, im Notfall Deinen <a href="%s">API-Key zurücksetzen</a>).'),
('inc_schicht_ical_text', 'EN', 'Export of shown shifts. <a href="%s">iCal format</a> or <a href="%s">JSON format</a> available (please keep secret, otherwise <a href="%s">reset the api key</a>).'),
('helpers', 'DE', 'Helfer'),
('helpers', 'EN', 'helpers'),
('helper', 'DE', 'Helfer'),
('helper', 'EN', 'helper'),
('needed', 'DE', 'gebraucht'),
('needed', 'EN', 'needed'),
('pub_myshifts_intro', 'DE', 'Hier sind Deine Schichten.<br/>Versuche bitte <b>15 Minuten</b> vor Schichtbeginn anwesend zu sein!<br/>Du kannst Dich %d Stunden vor Schichtbeginn noch aus Schichten wieder austragen.'),
('pub_myshifts_intro', 'EN', 'These are your shifts.<br/>Please try to appear <b>15 minutes</b> before your shift begins!<br/>You can remove yourself from a shift up to %d hours before it starts.'),
('pub_myshifts_goto_shifts', 'DE', 'Gehe zum <a href="%s">Schichtplan</a> um Dich für Schichten einzutragen.'),
('pub_myshifts_goto_shifts', 'EN', 'Go to the <a href="%s">shifts table</a> to sign yourself up for some shifts.'),
('pub_myshifts_signed_off', 'DE', 'Du wurdest aus der Schicht ausgetragen.'),
('pub_myshifts_signed_off', 'EN', 'You have been signed off from the shift.'),
('pub_myshifts_too_late', 'DE', 'Es ist zu spät um sich aus der Schicht auszutragen. Frage ggf. den Schichtkoordinator, ob er dich austragen kann.'),
('pub_myshifts_too_late', 'EN', 'It''s too late to sign yourself off the shift. If neccessary, as the dispatcher to do so.'),
('sign_off', 'DE', 'austragen'),
('sign_off', 'EN', 'sign out'),
('occupied', 'DE', 'belegt'),
('occupied', 'EN', 'occupied'),
('free', 'DE', 'frei'),
('free', 'EN', 'free'),
('edit', 'DE', 'bearbeiten'),
('edit', 'EN', 'edit');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `Nick` varchar(23) NOT NULL DEFAULT '',
  `Name` varchar(23) DEFAULT NULL,
  `Vorname` varchar(23) DEFAULT NULL,
  `Alter` int(4) DEFAULT NULL,
  `Telefon` varchar(40) DEFAULT NULL,
  `DECT` varchar(5) DEFAULT NULL,
  `Handy` varchar(40) DEFAULT NULL,
  `email` varchar(123) DEFAULT NULL,
  `ICQ` varchar(30) DEFAULT NULL,
  `jabber` varchar(200) DEFAULT NULL,
  `Size` varchar(4) DEFAULT NULL,
  `Passwort` varchar(128) DEFAULT NULL,
  `Gekommen` tinyint(4) NOT NULL DEFAULT '0',
  `Aktiv` tinyint(4) NOT NULL DEFAULT '0',
  `Tshirt` tinyint(4) DEFAULT '0',
  `color` tinyint(4) DEFAULT '10',
  `Sprache` char(2) DEFAULT 'EN',
  `Avatar` int(11) DEFAULT '0',
  `Menu` char(1) NOT NULL DEFAULT 'L',
  `lastLogIn` int(11) NOT NULL,
  `CreateDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Art` varchar(30) DEFAULT NULL,
  `kommentar` text,
  `Hometown` varchar(255) NOT NULL DEFAULT '',
  `api_key` varchar(32) NOT NULL,
  PRIMARY KEY (`UID`,`Nick`),
  UNIQUE KEY `Nick` (`Nick`),
  KEY `api_key` (`api_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `User`
--

INSERT INTO `User` (`UID`, `Nick`, `Name`, `Vorname`, `Alter`, `Telefon`, `DECT`, `Handy`, `email`, `ICQ`, `jabber`, `Size`, `Passwort`, `Gekommen`, `Aktiv`, `Tshirt`, `color`, `Sprache`, `Avatar`, `Menu`, `lastLogIn`, `CreateDate`, `Art`, `kommentar`, `Hometown`, `api_key`) VALUES
(1, 'admin', 'Gates', 'Bill', 42, '', '', '', '', '', '', '', '$23PstrXfk7Nw', 1, 1, 0, 10, 'DE', 115, 'L', 1371899094, '0000-00-00 00:00:00', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserAngelTypes`
--

DROP TABLE IF EXISTS `UserAngelTypes`;
CREATE TABLE IF NOT EXISTS `UserAngelTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `angeltype_id` int(11) NOT NULL,
  `confirm_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`angeltype_id`,`confirm_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `UserAngelTypes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserGroups`
--

DROP TABLE IF EXISTS `UserGroups`;
CREATE TABLE IF NOT EXISTS `UserGroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Daten für Tabelle `UserGroups`
--

INSERT INTO `UserGroups` (`id`, `uid`, `group_id`) VALUES
(1, 1, -2),
(2, 1, -3),
(3, 1, -6),
(4, 1, -5),
(12, 1, -4),
(15, 2, -2),
(16, 2, -3),
(17, 2, -4),
(18, 2, -5),
(19, 2, -6),
(21, 3, -2),
(22, 3, -5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Wecken`
--

DROP TABLE IF EXISTS `Wecken`;
CREATE TABLE IF NOT EXISTS `Wecken` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Date` int(11) NOT NULL,
  `Ort` text NOT NULL,
  `Bemerkung` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `Wecken`
--

