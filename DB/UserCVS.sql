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
-- Tabellenstruktur f?r Tabelle `UserCVS`
-- 
DROP TABLE IF EXISTS `UserCVS`;
CREATE TABLE `UserCVS` (
  `UID` int(11) NOT NULL default '0',
  `GroupID` int(11) default NULL,
  `index.php` char(1) NOT NULL default 'G',
  `logout.php` char(1) NOT NULL default 'G',
  `faq.php` char(1) NOT NULL default 'G',
  `lageplan.php` char(1) NOT NULL default 'G',
  `makeuser.php` char(1) NOT NULL default 'G',
  `nonpublic/index.php` char(1) NOT NULL default 'G',
  `nonpublic/news.php` char(1) NOT NULL default 'G',
  `nonpublic/newsAddMeting` char(1) NOT NULL default 'G',
  `nonpublic/news_comments.php` char(1) NOT NULL default 'G',
  `nonpublic/myschichtplan.php` char(1) NOT NULL default 'G',
  `nonpublic/myschichtplan_ical.php` char(1) NOT NULL default 'G',
  `nonpublic/engelbesprechung.php` char(1) NOT NULL default 'G',
  `nonpublic/schichtplan.php` char(1) NOT NULL default 'G',
  `nonpublic/schichtplan_add.php` char(1) NOT NULL default 'G',
  `nonpublic/wecken.php` char(1) NOT NULL default 'G',
  `nonpublic/waeckliste.php` char(1) NOT NULL default 'G',
  `nonpublic/messages.php` char(1) NOT NULL default 'G',
  `nonpublic/faq.php` char(1) NOT NULL default 'G',
  `nonpublic/einstellungen.php` char(1) NOT NULL default 'G',
  `Change T_Shirt Size` char(1) NOT NULL default 'G',
  `admin/index.php` char(1) NOT NULL default 'G',
  `admin/room.php` char(1) NOT NULL default 'G',
  `admin/EngelType.php` char(1) NOT NULL default 'G',
  `admin/schichtplan.php` char(1) NOT NULL default 'G',
  `admin/shiftadd.php` char(1) NOT NULL default 'G',
  `admin/schichtplan_druck.php` char(1) NOT NULL default 'G',
  `admin/user.php` char(1) NOT NULL default 'G',
  `admin/userChangeNormal.php` char(1) NOT NULL default 'G',
  `admin/userChangeSecure.php` char(1) NOT NULL default 'G',
  `admin/userSaveNormal.php` char(1) NOT NULL default 'G',
  `admin/userSaveSecure.php` char(1) NOT NULL default 'G',
  `admin/group.php` char(1) NOT NULL default 'G',
  `admin/userDefaultSetting.php` char(1) NOT NULL default 'G',
  `admin/UserPicture.php` char(1) NOT NULL default 'G',
  `admin/userArrived.php` char(1) NOT NULL default 'G',
  `admin/aktiv.php` char(1) NOT NULL default 'G',
  `admin/tshirt.php` char(1) NOT NULL default 'G',
  `admin/news.php` char(1) NOT NULL default 'G',
  `admin/faq.php` char(1) NOT NULL default 'G',
  `admin/free.php` char(1) NOT NULL default 'G',
  `admin/sprache.php` char(1) NOT NULL default 'G',
  `admin/dect.php` char(1) NOT NULL default 'G',
  `admin/dect_call.php` char(1) NOT NULL default 'G',
  `admin/dbUpdateFromXLS.php` char(1) NOT NULL default 'G',
  `admin/Recentchanges.php` char(1) NOT NULL default 'G',
  `admin/debug.php` char(1) NOT NULL default 'G',
  `Herald` char(1) NOT NULL default 'G',
  `Info` char(1) NOT NULL default 'G',
  `Conference` char(1) NOT NULL default 'G',
  PRIMARY KEY  (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `UserCVS`
-- 

