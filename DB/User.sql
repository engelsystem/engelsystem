--
-- Tabellenstruktur für Tabelle `User`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 16:38
-- Letzter Check am: 16. September 2005 um 19:24
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `UID` int(11) NOT NULL auto_increment,
  `Nick` varchar(23) NOT NULL default '',
  `Name` varchar(23) default NULL,
  `Vorname` varchar(23) default NULL,
  `Alter` int(4) default NULL,
  `Telefon` varchar(40) default NULL,
  `DECT` varchar(4) default NULL,
  `Handy` varchar(40) default NULL,
  `email` varchar(123) default NULL,
  `ICQ` varchar(30) default NULL,
  `jabber` varchar(200) default NULL,
  `Size` varchar(4) default NULL,
  `Passwort` varchar(40) default NULL,
  `Gekommen` tinyint(4) NOT NULL default '0',
  `Aktiv` tinyint(4) NOT NULL default '0',
  `Tshirt` tinyint(4) default '0',
  `color` tinyint(4) default '1',
  `Sprache` char(2) default 'EN',
  `Avatar` int(11) default '0',
  `Menu` char(1) NOT NULL default 'L',
  `lastLogIn` datetime NOT NULL default '0000-00-00 00:00:00',
  `CreateDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Art` varchar(30) default NULL,
  `kommentar` text,
  `Hometown` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`UID`,`Nick`),
  UNIQUE KEY `Nick` (`Nick`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=144 ;

INSERT INTO `User` (`UID`, `Nick`, `Name`, `Vorname`, `Alter`, `Telefon`, `DECT`, `Handy`, `email`, `Size`, `Passwort`, `Gekommen`, `Aktiv`, `Tshirt`, `color`, `Sprache`, `Avatar`, `lastLogIn`, `Art`, `kommentar`) VALUES (1, 'admin', '', '', 0, '', '', '', '', '', '21232f297a57a5a743894a0e4a801fc3', 0, 0, 0, 6, 'EN', 115, '0000-00-00 00:00:00', '', '');

