-- phpMyAdmin SQL Dump
-- version 2.6.2-Debian-3sarge1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 02. Dezember 2006 um 09:28
-- Server Version: 4.0.24
-- PHP-Version: 4.3.10-16
--
-- Datenbank: `Himmel`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserCVS`
--

DROP TABLE IF EXISTS `UserCVS`;
CREATE TABLE `UserCVS` (
  `UID` int(11) NOT NULL default '0',
  `index.php` char(1) NOT NULL default 'Y',
  `logout.php` char(1) NOT NULL default 'Y',
  `faq.php` char(1) NOT NULL default 'Y',
  `lageplan.php` char(1) NOT NULL default 'Y',
  `makeuser.php` char(1) NOT NULL default 'Y',

  `nonpublic/index.php` char(1) NOT NULL default 'Y',
  `nonpublic/news.php` char(1) NOT NULL default 'Y',
  `nonpublic/newsAddMeting` char(1) NOT NULL default 'N',
  `nonpublic/news_comments.php` char(1) NOT NULL default 'Y',
  `nonpublic/myschichtplan.php` char(1) NOT NULL default 'Y',
  `nonpublic/engelbesprechung.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan_add.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan_beamer.php` char(1) NOT NULL default 'Y',
  `nonpublic/wecken.php` char(1) NOT NULL default 'N',
  `nonpublic/waeckliste.php` char(1) NOT NULL default 'N',
  `nonpublic/messages.php` char(1) NOT NULL default 'Y',
  `nonpublic/faq.php` char(1) NOT NULL default 'Y',
  `nonpublic/einstellungen.php` char(1) NOT NULL default 'Y',
  `admin/index.php` char(1) NOT NULL default 'N',
  `admin/room.php` char(1) NOT NULL default 'N',
  `admin/EngelType.php` char(1) NOT NULL default 'N',
  `admin/schichtplan.php` char(1) NOT NULL default 'N',
  `admin/shiftadd.php` char(1) NOT NULL default 'N',
  `admin/schichtplan_druck.php` char(1) NOT NULL default 'N',
  `admin/user.php` char(1) NOT NULL default 'N',
  `admin/user2.php` char(1) NOT NULL default 'N',
  `admin/userDefaultSetting.php` char(1) NOT NULL default 'N',
  `admin/aktiv.php` char(1) NOT NULL default 'N',
  `admin/tshirt.php` char(1) NOT NULL default 'N',
  `admin/news.php` char(1) NOT NULL default 'N',
  `admin/faq.php` char(1) NOT NULL default 'N',
  `admin/free.php` char(1) NOT NULL default 'N',
  `admin/sprache.php` char(1) NOT NULL default 'N',
  `admin/dect.php` char(1) NOT NULL default 'N',
  `admin/dbUpdateFromXLS.php` char(1) NOT NULL default 'N',
  `admin/Recentchanges.php` char(1) NOT NULL default 'N',
  `admin/debug.php` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`UID`)
) TYPE=MyISAM;

--
-- Daten für Tabelle `UserCVS`
--

INSERT INTO `UserCVS` (`UID`, `index.php`, `logout.php`, `faq.php`, `lageplan.php`, `makeuser.php`, `nonpublic/index.php`, `nonpublic/news.php`, `nonpublic/newsAddMeting`, `nonpublic/news_comments.php`, `nonpublic/myschichtplan.php`, `nonpublic/engelbesprechung.php`, `admin/index.php`, `nonpublic/schichtplan.php`, `nonpublic/schichtplan_add.php`, `nonpublic/schichtplan_beamer.php`, `nonpublic/wecken.php`, `nonpublic/waeckliste.php`, `nonpublic/faq.php`, `nonpublic/einstellungen.php`, `admin/debug.php`, `admin/dbUpdateFromXLS.php`, `admin/room.php`, `admin/EngelType.php`, `admin/schichtplan.php`, `admin/shiftadd.php`, `admin/schichtplan_druck.php`, `admin/userDefaultSetting.php`, `admin/user.php`, `admin/user2.php`, `admin/aktiv.php`, `admin/tshirt.php`, `admin/news.php`, `admin/faq.php`, `admin/free.php`, `admin/sprache.php`, `admin/dect.php`) VALUES (-1, 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'), (1, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
