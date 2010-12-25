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
(2, 'Was bekomme ich f&uuml;r meine Mitarbeit?<br>\r\nWhat can i expect in return for my help? \r\n', 'Jeder Engel der arbeitet bekommt ein kostenloses T-Shirt nach der Veranstalltung <br>\r\nEvery working angel gets a free shirt after the event. '),
(3, 'Wie lange muss ich als Engel arbeiten?<br>\r\nHow long do I have to work as an angel ?', 'Diese Frage ist schwer zu beantworten. Es h&auml;ngt z.B. davon ab, was man macht (z.B. Workshop-Engel) und wieviele Engel wir zusammen bekommen. <br>\r\nThis is difficult to answer. It depends on what you decide to do (e.g. workshop angel) and how many people will attend. '),
(6, 'Ich bin erst XX Jahre alt. Kann ich &uuml;berhaupt helfen?<br>\r\nI''m only XX years old. Can I help anyway?', 'Wir k&ouml;nnen jede helfende Hand gebrauchen. Wenn du alt genug bist, um zum Congress zu kommen, bist du auch alt genug zu helfen. <br>\r\nWe need every help we can get. If your old enough to come to the congress, your old enough to help.'),
(8, 'Wer sind eigentlich die Erzengel?<br>\r\nWho <b>are</b> the Arch-Angels?\r\n', 'Erzengel sind dieses Jahr: BugBlue, TabascoEye, Jeedi, Daizy, volty<br> \r\nThe ArchAngels for this year are: BugBlue, TabascoEye, Jeedi, Daizy, volty\r\n'),
(9, 'Gibt es dieses Jahr wieder einen IRC-Channel f&uuml;r Engel?<br>\r\nWill there be an IRC-channel for angels again?', 'Ja, im IRC-Net existiert #chaos-angel. Einfach mal reinschaun!<br>\r\nYes, in the IRC-net there''s #chaos-angel. Just have a look!'),
(10, 'Wie gehe ich mit den Besuchern um? <br>\r\nHow do I treat visitors?', 'Man soll gegen&uuml;ber den Besuchern immer h&ouml;flich und freundlich sein, auch wenn diese gestresst sind. Wenn man das Gef&uuml;hl hat, dass man mit der Situation nicht mehr klarkommt, sollte man sich jemanden zur Unterst&uuml;tzung holen, bevor man selbst auch gestresst wird :-)  <br>\r\nYou should always be polite and friendly, especially if they are stressed. When you feel you can''t handle it on your own, get someone to help you out before you get so stressed yourself that you get impolite.'),
(11, 'Wann sind die Engelbesprechungen? <br>\r\nWhen are the angels briefings?', 'Das wird vor Ort noch festgelegt und steht im Himmelnewssystem.<br>\r\nThe information on the Angel Briefings will be in the news section of this system.'),
(12, 'Was muss ich noch bedenken?<br>\r\nAnything else I should know?', 'Man sollte nicht total &uuml;berm&uuml;det oder ausgehungert, wenn n man einen Einsatz hat. Eine gewisse Fitness ist hilfreich.<br>\r\nYou should not be exhausted or starving when you arrive for a shift. A reasonable amount of fitness for work would be very helpful.'),
(13, 'Ich habe eine Frage, auf die ich in der FAQ keine Antwort gefunden habe. Wohin soll ich mich wenden? <br>\r\nI have a guestion not answered here. Who can I ask?', 'Bei weitere Fragen kannst du die Anfragen an die Erzengel Formular benutzen.<br>\r\nIf you have further questions, you can use the Questions for the ArchAngels form.'),
(20, 'Wer muss alles Eintritt zahlen?<br>\r\nWho has to pay the full entrance price?', 'Jeder. Zumindest, solange er/sie &auml;lter als 12 Jahre ist...<br>\r\nEveryone who is at older than 12 years old.');

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

--
-- Tabellenstruktur für Tabelle `Counter`
--

DROP TABLE IF EXISTS `Counter`;
CREATE TABLE IF NOT EXISTS `Counter` (
  `URL` varchar(255) NOT NULL default '',
   `Anz` bigint(20) NOT NULL default '0',
   PRIMARY KEY  (`URL`)
) TYPE=MyISAM COMMENT='Counter der Seiten';

--
-- Tabellenstruktur für Tabelle `ShiftFreeloader`
--

CREATE TABLE IF NOT EXISTS `ShiftFreeloader` (
  `ID` int(11) NOT NULL auto_increment,
  `Remove_Time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UID` int(11) NOT NULL,
  `Length` int(11) NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

