-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 06. November 2005 um 18:08
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
-- Aktualisiert am: 06. November 2005 um 01:15
--

DROP TABLE IF EXISTS `EngelType`;
CREATE TABLE IF NOT EXISTS `EngelType` (
  `TID` int(11) NOT NULL auto_increment,
  `Name` varchar(25) NOT NULL default '',
  `Man` text,
  PRIMARY KEY  (`TID`),
  UNIQUE KEY `Name` (`Name`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `FAQ`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 17:02
--

DROP TABLE IF EXISTS `FAQ`;
CREATE TABLE IF NOT EXISTS `FAQ` (
  `FID` bigint(20) NOT NULL auto_increment,
  `Frage` text NOT NULL,
  `Antwort` text NOT NULL,
  PRIMARY KEY  (`FID`)
) TYPE=MyISAM AUTO_INCREMENT=24 ;

--
-- Daten für Tabelle `FAQ`
--

INSERT INTO `FAQ` (`FID`, `Frage`, `Antwort`) VALUES (1, 'Komme ich als Engel billiger/kostenlos auf den Congress?<br>\r\nDo I get in cheaper / for free to the congress as an angel ?', 'Nein, jeder Engel muss normal Eintritt bezahlen.<br>\r\nNo, every angel has to pay full price.'),
(2, 'Was bekomme ich für meine Mitarbeit?<br>\r\nWhat do I get for helping ? \r\n', 'Jeder Engel der arbeitet bekommt ein kostenloses (Camp-)T-Shirt nach der Veranstalltung <br>\r\nEvery working angel gets a free (Camp-) shirt after the event. '),
(3, 'Wie lange muss ich als Engel arbeiten?<br>\r\nHow long do I have to work as an angel ?', 'Diese Frage ist schwer zu beantworten. Es hängt z.B. davon ab, was man macht (z.B. Workshop-Engel) und wieviele Engel wir zusammenbekommen. <br>\r\nThis is difficult to answer. It depends on what you''ll have to do (e.g. workshop angel) and how many angels will be there. '),
(6, 'Ich bin erst XX Jahre alt. Kann ich überhaupt helfen?<br>\r\nI''m only XX years old. Can I help anyway?', 'Du bist alt genug, zum Camp zu kommen? Dann bist Du alt genug zu helfen. <br>\r\nYou''re old enough to come to the Camp? So you''re old enough to help, too.'),
(8, 'Wer ist eigentlich alles Erzengel?<br>\r\nWho <b>are</b> the Arch-Angels?\r\n', 'Erzengel sind dieses Jahr: Daizy, Flip, Hasi, Enno, Nachtkind und SaniFox. <br>\r\nThe ArchAngels for this year are: Daizy, Flip, Hasi, Enno, Nachtkind und SaniFox. \r\n'),
(9, 'Gibt es dieses Jahr wieder einen IRC-Channel für Engel?<br>\r\nWill there be an IRC-channel for angels again?', 'Ja, im IRC-Net existiert #congress-engel. Einfach mal reinschaun!<br>\r\nYes, in the IRC-net there''s #congress-engel. Just have a look!'),
(10, 'Wie gehe ich mit den Besuchern um? <br>\r\nHow do I treat visitors?', 'Man soll gegenüber den Besuchern immer höflich und freundlich sein, auch wenn diese gestresst sind. Wenn man das Gefühl hat, dass man mit der Situation nicht mehr klarkommt, sollte man sich jemanden zur Unterstützung holen, bevor man selbst auch gestresst wird :-)  <br>\r\nYou should always be polite and friendly, especially if they are stressed. When you feel you can''t handle it on your own, get someone to help you out before you get so stressed yourself that you get impolite.'),
(11, 'Wann sind die Engelbesprechungen? <br>\r\nWhen are the angels briefings?', 'Das wird vor Ort noch festgelegt und steht im Himmelnewssystem.<br>\r\nThe information on the Angel Briefings will be in the news section of this system.'),
(12, 'Was muss ich noch bedenken?<br>\r\nAnything else I should know?', 'Man sollte nicht total übermüdet oder ausgehungert, wenn man einen Einsatz hat. Eine gewisse Fitness ist hilfreich.<br>\r\nYou should not be exhausted or starving when you arrive for a shift. A reasonable amount of fitness for work would be very helpful.'),
(13, 'Ich habe eine Frage, auf die ich in der FAQ keine Antwort gefunden habe. Wohin soll ich mich wenden? <br>\r\nI have a guestion not answered here. Who can I ask?', 'Bei weitere Fragen kannst du die Anfragen an die Erzengel Formular benutzen.<br>\r\nIf you have further questions, you can use the Questions for the ArchAngels form.'),
(20, 'Wer muss alles Eintritt zahlen?&lt;br&gt;\r\nWho has to pay the full entrance price?', 'Jeder. Zumindest, solange er/sie älter als 12 Jahre ist...&lt;br&gt;\r\n&lt;b&gt;Everyone&lt;/b&gt; who is at older than 12 years old.');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `News`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 16:50
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Questions`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 17:01
--

DROP TABLE IF EXISTS `Questions`;
CREATE TABLE IF NOT EXISTS `Questions` (
  `QID` bigint(20) NOT NULL auto_increment,
  `UID` int(11) NOT NULL default '0',
  `Question` text NOT NULL,
  `AID` int(11) NOT NULL default '0',
  `Answer` text NOT NULL,
  PRIMARY KEY  (`QID`)
) TYPE=MyISAM COMMENT='Fragen und Antworten' AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Room`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 01:04
--

DROP TABLE IF EXISTS `Room`;
CREATE TABLE IF NOT EXISTS `Room` (
  `RID` int(11) NOT NULL auto_increment,
  `Name` varchar(35) NOT NULL default '',
  `Man` text,
  `FromPentabarf` char(1) NOT NULL default 'N',
  `show` char(1) NOT NULL default 'Y',
  `Number` int(11) default NULL,
  PRIMARY KEY  (`RID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftEntry`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 15:54
-- Letzter Check am: 16. September 2005 um 19:24
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
-- Aktualisiert am: 06. November 2005 um 16:03
-- Letzter Check am: 16. September 2005 um 19:24
--

DROP TABLE IF EXISTS `Shifts`;
CREATE TABLE IF NOT EXISTS `Shifts` (
  `SID` int(11) NOT NULL auto_increment,
  `DateS` datetime NOT NULL default '0000-00-00 00:00:00',
  `DateE` datetime NOT NULL default '0000-00-00 00:00:00',
  `Len` float NOT NULL default '0',
  `RID` int(11) NOT NULL default '0',
  `Man` text,
  `URL` text,
  `PSID` text,
  PRIMARY KEY  (`SID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 16:38
-- Letzter Check am: 16. September 2005 um 19:24
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `User` (`UID`, `Nick`, `Name`, `Vorname`, `Alter`, `Telefon`, `DECT`, `Handy`, `email`, `Size`, `Passwort`, `Gekommen`, `Aktiv`, `Tshirt`, `color`, `Sprache`, `Avatar`, `lastLogIn`, `Art`, `kommentar`) VALUES (1, 'admin', '', '', 0, '', '', '', '', '', '21232f297a57a5a743894a0e4a801fc3', 0, 0, 0, 6, 'EN', 115, '0000-00-00 00:00:00', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserCVS`
--
-- Erzeugt am: 06. November 2005 um 17:47
-- Aktualisiert am: 06. November 2005 um 18:00
--

DROP TABLE IF EXISTS `UserCVS`;
CREATE TABLE IF NOT EXISTS `UserCVS` (
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
  `nonpublic/faq.php` char(1) NOT NULL default 'Y',
  `nonpublic/einstellungen.php` char(1) NOT NULL default 'Y',
  `admin/index.php` char(1) NOT NULL default 'N',
  `admin/debug.php` char(1) NOT NULL default 'N',
  `admin/dbUpdateFromXLS.php` char(1) NOT NULL default 'N',
  `admin/room.php` char(1) NOT NULL default 'N',
  `admin/EngelType.php` char(1) NOT NULL default 'N',
  `admin/schichtplan.php` char(1) NOT NULL default 'N',
  `admin/shiftadd.php` char(1) NOT NULL default 'N',
  `admin/schichtplan_druck.php` char(1) NOT NULL default 'N',
  `admin/userDefaultSetting.php` char(1) NOT NULL default 'N',
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
  PRIMARY KEY  (`UID`)
) TYPE=MyISAM;

--
-- Daten für Tabelle `UserCVS`
--

INSERT INTO `UserCVS` (`UID`, `index.php`, `logout.php`, `faq.php`, `lageplan.php`, `makeuser.php`, `nonpublic/index.php`, `nonpublic/news.php`, `nonpublic/newsAddMeting`, `nonpublic/news_comments.php`, `nonpublic/myschichtplan.php`, `nonpublic/engelbesprechung.php`, `admin/index.php`, `nonpublic/schichtplan.php`, `nonpublic/schichtplan_add.php`, `nonpublic/schichtplan_beamer.php`, `nonpublic/wecken.php`, `nonpublic/waeckliste.php`, `nonpublic/faq.php`, `nonpublic/einstellungen.php`, `admin/debug.php`, `admin/dbUpdateFromXLS.php`, `admin/room.php`, `admin/EngelType.php`, `admin/schichtplan.php`, `admin/shiftadd.php`, `admin/schichtplan_druck.php`, `admin/userDefaultSetting.php`, `admin/user.php`, `admin/user2.php`, `admin/aktiv.php`, `admin/tshirt.php`, `admin/news.php`, `admin/faq.php`, `admin/free.php`, `admin/sprache.php`, `admin/dect.php`, `Netz`, `Kassen`) VALUES (-1, 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'), (1, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Wecken`
--
-- Erzeugt am: 25. März 2005 um 12:16
-- Aktualisiert am: 06. November 2005 um 00:21
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

