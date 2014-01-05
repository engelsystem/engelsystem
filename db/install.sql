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
(14, 'admin_news', 'Administrate the news section'),
(15, 'news_comments', 'User can comment news'),
(16, 'admin_user', 'Administrate the angels'),
(17, 'user_meetings', 'Lists meetings (news)'),
(18, 'admin_language', 'Translate the system'),
(19, 'admin_log', 'Display recent changes'),
(20, 'user_wakeup', 'User wakeup-service organization'),
(21, 'admin_import', 'Import rooms and shifts from pentabarf'),
(22, 'credits', 'View credits'),
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
  `Sprache` char(64) DEFAULT 'EN',
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
(1, 'admin', 'Gates', 'Bill', 42, '', '', '', '', '', '', '', '21232f297a57a5a743894a0e4a801fc3', 1, 1, 0, 1, 'DE', 115, 'L', 1371899094, '0000-00-00 00:00:00', '', '', '', '');

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

