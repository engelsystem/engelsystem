-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `UserCVS`
-- 
DROP TABLE IF EXISTS `UserCVS`;
CREATE TABLE `UserCVS` (
  `UID` int(11) NOT NULL default '0',
  `GroupID` int(11) default '-2',
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
  `nonpublic/schichtplan_beamer.php` char(1) NOT NULL default 'G',
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
  `admin/userSaveNormal.php` char(1) NOT NULL default 'G',
  `admin/userChangeSecure.php` char(1) NOT NULL default 'G',
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
  `Kasse` char(1) NOT NULL default 'G',
  PRIMARY KEY  (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten für Tabelle `UserCVS`
-- 

INSERT INTO `UserCVS` (`UID`, `GroupID`, `index.php`, `logout.php`, `faq.php`, `lageplan.php`, `makeuser.php`, `nonpublic/index.php`, `nonpublic/news.php`, `nonpublic/newsAddMeting`, `nonpublic/news_comments.php`, `nonpublic/myschichtplan.php`, `nonpublic/myschichtplan_ical.php`, `nonpublic/schichtplan_beamer.php`, `nonpublic/engelbesprechung.php`, `nonpublic/schichtplan.php`, `nonpublic/schichtplan_add.php`, `nonpublic/wecken.php`, `nonpublic/waeckliste.php`, `nonpublic/messages.php`, `nonpublic/faq.php`, `nonpublic/einstellungen.php`, `Change T_Shirt Size`, `admin/index.php`, `admin/room.php`, `admin/EngelType.php`, `admin/schichtplan.php`, `admin/shiftadd.php`, `admin/schichtplan_druck.php`, `admin/user.php`, `admin/userChangeNormal.php`, `admin/userSaveNormal.php`, `admin/userChangeSecure.php`, `admin/userSaveSecure.php`, `admin/group.php`, `admin/userDefaultSetting.php`, `admin/UserPicture.php`, `admin/userArrived.php`, `admin/aktiv.php`, `admin/tshirt.php`, `admin/news.php`, `admin/faq.php`, `admin/free.php`, `admin/sprache.php`, `admin/dect.php`, `admin/dect_call.php`, `admin/dbUpdateFromXLS.php`, `admin/Recentchanges.php`, `admin/debug.php`, `Herald`, `Info`, `Conference`, `Kasse`) VALUES 
(1, -4, 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G'),
(-1, NULL, 'Y', 'N', 'Y', 'N', 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'),
(-2, NULL, 'N', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'),
(-3, NULL, 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'Y', 'Y', 'Y', 'Y'),
(-4, NULL, 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y'),
(-5, NULL, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');

-- --------------------------------------------------------
-- --------------------------------------------------------
-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `UserGroups`
-- 

DROP TABLE IF EXISTS `UserGroups`;
CREATE TABLE IF NOT EXISTS `UserGroups` (
  `Name` varchar(35) NOT NULL,
  `UID` int(11) NOT NULL,
  PRIMARY KEY  (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten für Tabelle `UserGroups`
-- 

INSERT INTO `UserGroups` (`Name`, `UID`) VALUES 
('1-logout User', -1),
('2-Engel', -2),
('3-Shift Coordinator', -3),
('4-Erzengel', -4),
('5-Developer', -5);

