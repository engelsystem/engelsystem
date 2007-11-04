-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 09. Oktober 2007 um 21:53
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch7
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
  `index.php` char(1) NOT NULL default 'N',
  `logout.php` char(1) NOT NULL default 'Y',
  `faq.php` char(1) NOT NULL default 'Y',
  `lageplan.php` char(1) NOT NULL default 'N',
  `makeuser.php` char(1) NOT NULL default 'N',
  `nonpublic/index.php` char(1) NOT NULL default 'Y',
  `nonpublic/news.php` char(1) NOT NULL default 'Y',
  `nonpublic/newsAddMeting` char(1) NOT NULL default 'N',
  `nonpublic/news_comments.php` char(1) NOT NULL default 'Y',
  `nonpublic/myschichtplan.php` char(1) NOT NULL default 'Y',
  `nonpublic/engelbesprechung.php` char(1) NOT NULL default 'Y',
  `nonpublic/schichtplan.php` char(1) NOT NULL default 'Y',
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
  `admin/UserPicture.php` char(1) NOT NULL default 'N',
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
  `Herald` char(1) NOT NULL default 'N',
  `Info` char(1) NOT NULL default 'N',
  `Conference` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten für Tabelle `UserCVS`
-- 

INSERT INTO `UserCVS` (`UID`, `index.php`, `logout.php`, `faq.php`, `lageplan.php`, `makeuser.php`, `nonpublic/index.php`, `nonpublic/news.php`, `nonpublic/newsAddMeting`, `nonpublic/news_comments.php`, `nonpublic/myschichtplan.php`, `nonpublic/engelbesprechung.php`, `nonpublic/schichtplan.php`, `nonpublic/wecken.php`, `nonpublic/waeckliste.php`, `nonpublic/messages.php`, `nonpublic/faq.php`, `nonpublic/einstellungen.php`, `admin/index.php`, `admin/room.php`, `admin/EngelType.php`, `admin/schichtplan.php`, `admin/shiftadd.php`, `admin/schichtplan_druck.php`, `admin/user.php`, `admin/user2.php`, `admin/userDefaultSetting.php`, `admin/UserPicture.php`, `admin/aktiv.php`, `admin/tshirt.php`, `admin/news.php`, `admin/faq.php`, `admin/free.php`, `admin/sprache.php`, `admin/dect.php`, `admin/dbUpdateFromXLS.php`, `admin/Recentchanges.php`, `admin/debug.php`, `Herald`, `Info`, `Conference`) VALUES

(-1, 'Y', 'N', 'Y', 'N', 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'),
--    1    2    3    4    5    6    7    8    9   10   11   12   13   14   15   16   17   18   19   20   21   22   23   24   25   26   27   28   29   30   31   32   33   34   35   36   37   38   39   40   41   42   43
(1, 'N', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N');

