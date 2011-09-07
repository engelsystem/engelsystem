-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 07. September 2011 um 21:23
-- Server Version: 5.1.44
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `engelsystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `AngelTypes`
--

CREATE TABLE IF NOT EXISTS `AngelTypes` (
  `TID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(25) NOT NULL DEFAULT '',
  `Man` text,
  PRIMARY KEY (`TID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `AngelTypes`
--

INSERT INTO `AngelTypes` (`TID`, `Name`, `Man`) VALUES
(4, 'Audio', ''),
(5, 'Massage', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ChangeLog`
--

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

CREATE TABLE IF NOT EXISTS `Counter` (
  `URL` varchar(255) NOT NULL DEFAULT '',
  `Anz` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`URL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Counter der Seiten';

--
-- Daten für Tabelle `Counter`
--

INSERT INTO `Counter` (`URL`, `Anz`) VALUES
('news', 248),
('login', 89),
('logout', 22),
('start', 65),
('faq', 36),
('credits', 13),
('register', 24),
('admin_rooms', 123),
('admin_angel_types', 85),
('user_settings', 163),
('user_messages', 124),
('admin_groups', 196),
('user_questions', 63),
('admin_questions', 51),
('admin_faq', 61),
('admin_news', 35),
('news_comments', 158),
('admin_user', 225),
('user_meetings', 15),
('admin_language', 38),
('admin_log', 19),
('user_wakeup', 70),
('admin_import', 245),
('user_shifts', 414),
('user_myshifts', 101),
('admin_arrive', 89),
('admin_shifts', 145);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `FAQ`
--

CREATE TABLE IF NOT EXISTS `FAQ` (
  `FID` bigint(20) NOT NULL AUTO_INCREMENT,
  `Frage_de` text NOT NULL,
  `Antwort_de` text NOT NULL,
  `Frage_en` text NOT NULL,
  `Antwort_en` text NOT NULL,
  PRIMARY KEY (`FID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Daten für Tabelle `FAQ`
--

INSERT INTO `FAQ` (`FID`, `Frage_de`, `Antwort_de`, `Frage_en`, `Antwort_en`) VALUES
(1, 'Komme ich als Engel billiger/kostenlos auf den Congress?', 'Nein, jeder Engel muss normal Eintritt bezahlen.', 'Do I get in cheaper / for free to the congress as an angel ?', 'No, every angel has to pay full price.'),
(2, 'Was bekomme ich f&uuml;r meine Mitarbeit?', 'Jeder Engel der arbeitet bekommt ein kostenloses T-Shirt nach der Veranstalltung', 'What can i expect in return for my help?', 'Every working angel gets a free shirt after the event.'),
(3, 'Wie lange muss ich als Engel arbeiten?', 'Diese Frage ist schwer zu beantworten. Es h&auml;ngt z.B. davon ab, was man macht (z.B. Workshop-Engel) und wieviele Engel wir zusammen bekommen.', 'How long do I have to work as an angel ?', 'This is difficult to answer. It depends on what you decide to do (e.g. workshop angel) and how many people will attend.'),
(6, 'Ich bin erst XX Jahre alt. Kann ich &uuml;berhaupt helfen?', 'Wir k&ouml;nnen jede helfende Hand gebrauchen. Wenn du alt genug bist, um zum Congress zu kommen, bist du auch alt genug zu helfen.', 'I''m only XX years old. Can I help anyway?', 'We need every help we can get. If your old enough to come to the congress, your old enough to help.'),
(8, 'Wer sind eigentlich die Erzengel?', 'Erzengel sind dieses Jahr: BugBlue, TabascoEye, Jeedi, Daizy, volty', 'Who <b>are</b> the Arch-Angels?', 'The ArchAngels for this year are: BugBlue, TabascoEye, Jeedi, Daizy, volty'),
(9, 'Gibt es dieses Jahr wieder einen IRC-Channel f&uuml;r Engel?', 'Ja, im IRC-Net existiert #chaos-angel. Einfach mal reinschaun!', 'Will there be an IRC-channel for angels again?', 'Yes, in the IRC-net there''s #chaos-angel. Just have a look!'),
(10, 'Wie gehe ich mit den Besuchern um?', 'Man soll gegen&uuml;ber den Besuchern immer h&ouml;flich und freundlich sein, auch wenn diese gestresst sind. Wenn man das Gef&uuml;hl hat, dass man mit der Situation nicht mehr klarkommt, sollte man sich jemanden zur Unterst&uuml;tzung holen, bevor man selbst auch gestresst wird :-)', 'How do I treat visitors?', 'You should always be polite and friendly, especially if they are stressed. When you feel you can''t handle it on your own, get someone to help you out before you get so stressed yourself that you get impolite.'),
(11, 'Wann sind die Engelbesprechungen?', 'Das wird vor Ort noch festgelegt und steht im Himmelnewssystem.', 'When are the angels briefings?', 'The information on the Angel Briefings will be in the news section of this system.'),
(12, 'Was muss ich noch bedenken?', 'Man sollte nicht total &uuml;berm&uuml;det oder ausgehungert, wenn n man einen Einsatz hat. Eine gewisse Fitness ist hilfreich.', 'Anything else I should know?', 'You should not be exhausted or starving when you arrive for a shift. A reasonable amount of fitness for work would be very helpful.'),
(13, 'Ich habe eine Frage, auf die ich in der FAQ keine Antwort gefunden habe. Wohin soll ich mich wenden?', 'Bei weitere Fragen kannst du die Anfragen an die Erzengel Formular benutzen.', 'I have a guestion not answered here. Who can I ask?', 'If you have further questions, you can use the Questions for the ArchAngels form.'),
(20, 'Wer muss alles Eintritt zahlen?', 'Jeder. Zumindest, solange er/sie &auml;lter als 12 Jahre ist...', 'Who has to pay the full entrance price?', 'Everyone who is at older than 12 years old.');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `GroupPrivileges`
--

CREATE TABLE IF NOT EXISTS `GroupPrivileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`,`privilege_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=118 ;

--
-- Daten für Tabelle `GroupPrivileges`
--

INSERT INTO `GroupPrivileges` (`id`, `group_id`, `privilege_id`) VALUES
(107, -2, 24),
(24, -1, 5),
(106, -2, 8),
(105, -2, 11),
(23, -1, 2),
(116, -5, 16),
(115, -5, 28),
(104, -2, 26),
(103, -2, 9),
(86, -6, 21),
(114, -5, 6),
(113, -5, 12),
(102, -2, 17),
(112, -5, 14),
(111, -5, 13),
(110, -5, 7),
(101, -2, 15),
(87, -6, 18),
(100, -2, 3),
(85, -6, 10),
(99, -2, 4),
(88, -1, 1),
(98, -3, 25),
(108, -2, 20),
(109, -4, 27),
(117, -5, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Groups`
--

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
-- Tabellenstruktur für Tabelle `Messages`
--

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Fuers interen Communikationssystem' AUTO_INCREMENT=9 ;

--
-- Daten für Tabelle `Messages`
--

INSERT INTO `Messages` (`id`, `Datum`, `SUID`, `RUID`, `isRead`, `Text`) VALUES
(8, 1307985371, 1, 148, 'N', 'asdfasdfasdfasdfasdfasdfasdfasdf'),
(7, 1307042692, 147, 1, 'Y', 'foobar');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `NeededAngelTypes`
--

CREATE TABLE IF NOT EXISTS `NeededAngelTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `angel_type_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`,`angel_type_id`),
  KEY `shift_id` (`shift_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Daten für Tabelle `NeededAngelTypes`
--

INSERT INTO `NeededAngelTypes` (`id`, `room_id`, `shift_id`, `angel_type_id`, `count`) VALUES
(4, 3, NULL, 5, 2),
(3, 3, NULL, 4, 2),
(5, 2, NULL, 4, 0),
(6, 2, NULL, 5, 2),
(10, 11, NULL, 5, 0),
(9, 11, NULL, 4, 2),
(11, 10, NULL, 4, 2),
(12, 10, NULL, 5, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `News`
--

CREATE TABLE IF NOT EXISTS `News` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Datum` int(11) NOT NULL,
  `Betreff` varchar(150) NOT NULL DEFAULT '',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Treffen` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `News`
--

INSERT INTO `News` (`ID`, `Datum`, `Betreff`, `Text`, `UID`, `Treffen`) VALUES
(4, 1307076340, 'Achtung, Treffen!', '', 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news_comments`
--

CREATE TABLE IF NOT EXISTS `news_comments` (
  `ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `Refid` int(11) NOT NULL DEFAULT '0',
  `Datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Text` text NOT NULL,
  `UID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Refid` (`Refid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `news_comments`
--

INSERT INTO `news_comments` (`ID`, `Refid`, `Datum`, `Text`, `UID`) VALUES
(1, 10, '2011-06-03 04:12:28', 'FOobar :)', 1),
(2, 10, '2011-06-03 04:13:03', 'FOobar :)', 1),
(3, 10, '2011-06-03 04:13:06', 'FOobar :)', 1),
(4, 3, '2011-06-03 05:20:05', 'Fünününü!', 1),
(5, 4, '2011-07-13 13:22:17', 'asdfasdf', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Privileges`
--

CREATE TABLE IF NOT EXISTS `Privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `desc` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

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
(28, 'admin_shifts', 'Create shifts');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Questions`
--

CREATE TABLE IF NOT EXISTS `Questions` (
  `QID` bigint(20) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Question` text NOT NULL,
  `AID` int(11) NOT NULL DEFAULT '0',
  `Answer` text NOT NULL,
  PRIMARY KEY (`QID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Fragen und Antworten' AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `Questions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Room`
--

CREATE TABLE IF NOT EXISTS `Room` (
  `RID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(35) NOT NULL DEFAULT '',
  `Man` text,
  `FromPentabarf` char(1) NOT NULL DEFAULT 'N',
  `show` char(1) NOT NULL DEFAULT 'Y',
  `Number` int(11) DEFAULT NULL,
  PRIMARY KEY (`RID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `Room`
--

INSERT INTO `Room` (`RID`, `Name`, `Man`, `FromPentabarf`, `show`, `Number`) VALUES
(2, 'Mein Zimmer', 'msquare', 'N', 'Y', 1337),
(10, 'Kourou', '', 'Y', 'Y', 0),
(11, 'Baikonur', '', 'Y', 'Y', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftEntry`
--

CREATE TABLE IF NOT EXISTS `ShiftEntry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SID` int(11) NOT NULL DEFAULT '0',
  `TID` int(11) NOT NULL DEFAULT '0',
  `UID` int(11) NOT NULL DEFAULT '0',
  `Comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Daten für Tabelle `ShiftEntry`
--

INSERT INTO `ShiftEntry` (`id`, `SID`, `TID`, `UID`, `Comment`) VALUES
(14, 131, 4, 1, 'asdfasdfasdf');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ShiftFreeloader`
--

CREATE TABLE IF NOT EXISTS `ShiftFreeloader` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Remove_Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UID` int(11) NOT NULL,
  `Length` int(11) NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `ShiftFreeloader`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Shifts`
--

CREATE TABLE IF NOT EXISTS `Shifts` (
  `SID` int(11) NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `RID` int(11) NOT NULL DEFAULT '0',
  `name` varchar(1024) DEFAULT NULL,
  `URL` text,
  `PSID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SID`),
  UNIQUE KEY `PSID` (`PSID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=158 ;

--
-- Daten für Tabelle `Shifts`
--

INSERT INTO `Shifts` (`SID`, `start`, `end`, `RID`, `name`, `URL`, `PSID`) VALUES
(99, 1313089200, 1313092800, 10, 'Accessblocking/Internetsperren- #Censilia and beyond', 'https://events.ccc.de/camp/2011/Fahrplan/events/4462.en.html', 4462),
(100, 1313159400, 1313163000, 10, 'A modern manifest of cyberspace- The internet is dead, long live the internet', 'https://events.ccc.de/camp/2011/Fahrplan/events/4451.en.html', 4451),
(101, 1313175600, 1313179200, 11, 'Applied Research on security of TETRA radio- digital radio technology beyond GSM', 'https://events.ccc.de/camp/2011/Fahrplan/events/4496.en.html', 4496),
(102, 1313251200, 1313254800, 11, 'A short history of IPv4', 'https://events.ccc.de/camp/2011/Fahrplan/events/4497.en.html', 4497),
(103, 1313240400, 1313244000, 10, 'Avionics- Design and implementation of flight electronics', 'https://events.ccc.de/camp/2011/Fahrplan/events/4467.en.html', 4467),
(104, 1313181000, 1313184600, 11, 'Certified programming with dependent types- Because the future of defense is liberal application of math', 'https://events.ccc.de/camp/2011/Fahrplan/events/4426.en.html', 4426),
(105, 1313337600, 1313339400, 10, 'Closing Event- Good Bye and have a safe trip home!', 'https://events.ccc.de/camp/2011/Fahrplan/events/4554.en.html', 4554),
(106, 1313170200, 1313173800, 10, 'Counselling Mischief as Thought Crime- Social Networks, Free Speech and the Criminalization of Dissent in Canada', 'https://events.ccc.de/camp/2011/Fahrplan/events/4395.en.html', 4395),
(107, 1313326800, 1313330400, 11, 'Data Mining Your City- Early lessons in open city data from Philadelphia, PA, USA', 'https://events.ccc.de/camp/2011/Fahrplan/events/4445.en.html', 4445),
(108, 1313148600, 1313152200, 11, 'Decentralized clustering- Making the net - even if your local dicators hate it!', 'https://events.ccc.de/camp/2011/Fahrplan/events/4389.en.html', 4389),
(109, 1313067600, 1313073000, 11, 'Die psychologischen Grundlagen des Social Engineerings', 'https://events.ccc.de/camp/2011/Fahrplan/events/4478.en.html', 4478),
(110, 1313062200, 1313065800, 11, '"Digitale Gesellschaft e.V." - Ein neuer Ansatz, um digitale Bürgerrechte zu erhalten', 'https://events.ccc.de/camp/2011/Fahrplan/events/4449.en.html', 4449),
(111, 1313316000, 1313319600, 11, 'Dudle: Mehrseitig sichere Web 2.0-Umfragen', 'https://events.ccc.de/camp/2011/Fahrplan/events/4438.en.html', 4438),
(112, 1313056800, 1313060400, 10, 'Giving Great Workshops- You can create your own successful workshop', 'https://events.ccc.de/camp/2011/Fahrplan/events/4406.en.html', 4406),
(113, 1313159400, 1313163000, 11, 'GPRS Intercept- Wardriving phone networks', 'https://events.ccc.de/camp/2011/Fahrplan/events/4504.en.html', 4504),
(114, 1313272800, 1313280000, 10, 'Hacker Jeopardy- Number guessing for geeks', 'https://events.ccc.de/camp/2011/Fahrplan/events/4561.en.html', 4561),
(115, 1312972200, 1312974000, 10, 'Hackers in Space- A Modest Proposal for the Next 23 Years', 'https://events.ccc.de/camp/2011/Fahrplan/events/4551.en.html', 4551),
(116, 1313083800, 1313087400, 11, 'Hacking DNA- Compiling code for living systems', 'https://events.ccc.de/camp/2011/Fahrplan/events/4472.en.html', 4472),
(117, 1313148600, 1313152200, 10, 'Hybrid rocket engines- Design and implementation of rocket engines with two-phase propellants', 'https://events.ccc.de/camp/2011/Fahrplan/events/4447.en.html', 4447),
(118, 1313078400, 1313082000, 11, 'Ich und 23- Fingerabdrücke der DNA', 'https://events.ccc.de/camp/2011/Fahrplan/events/4503.en.html', 4503),
(119, 1313013600, 1313017200, 10, 'Ihr kotzt mich alle an.- Wir haben Probleme. Und ihr merkt es nicht mal.', 'https://events.ccc.de/camp/2011/Fahrplan/events/4423.en.html', 4423),
(120, 1313094600, 1313098200, 11, 'Imagine the Future of Money- Economic transformations, hacker culture and why we should be so lucky', 'https://events.ccc.de/camp/2011/Fahrplan/events/4450.en.html', 4450),
(121, 1313067600, 1313071200, 10, 'Inertial navigation- Rigid body dynamics and its application to dead reckoning', 'https://events.ccc.de/camp/2011/Fahrplan/events/4458.en.html', 4458),
(122, 1313316000, 1313319600, 10, 'Introduction to Multicast Security- Beyond SSL/TLS', 'https://events.ccc.de/camp/2011/Fahrplan/events/4495.en.html', 4495),
(123, 1312981200, 1312984800, 10, 'Introduction to Satellite Communications- Installation and Operation of Satellite Systems; illustrated with Postage Stamps', 'https://events.ccc.de/camp/2011/Fahrplan/events/4442.en.html', 4442),
(124, 1313089200, 1313092800, 11, 'iOS application security- a look at the security of 3rd party iOS applications', 'https://events.ccc.de/camp/2011/Fahrplan/events/4490.en.html', 4490),
(125, 1312986600, 1312990200, 11, 'Is this the Mobile Gadget World We Created?- The story of the world''s first socially responsible mobile phone.', 'https://events.ccc.de/camp/2011/Fahrplan/events/4502.en.html', 4502),
(126, 1313083800, 1313087400, 10, 'Latest developments around the Milkymist System-on-Chip- A roundup of one the most advanced open hardware projects', 'https://events.ccc.de/camp/2011/Fahrplan/events/4412.en.html', 4412),
(127, 1313235000, 1313238600, 11, 'Learning Secrets by Watching People- Gesture, Expression, and Behavior Analysis for Hackers', 'https://events.ccc.de/camp/2011/Fahrplan/events/4550.en.html', 4550),
(128, 1313073000, 1313076600, 10, 'Life foods- Benefits of use of microbial fermentations in food and beverage preparations.', 'https://events.ccc.de/camp/2011/Fahrplan/events/4429.en.html', 4429),
(129, 1313245800, 1313249400, 11, 'Machine-to-machine (M2M) security- When physical security depends on IT security', 'https://events.ccc.de/camp/2011/Fahrplan/events/4439.en.html', 4439),
(130, 1312970400, 1312972200, 10, 'Opening Event- Welcome to the Chaos Communication Camp 2011', 'https://events.ccc.de/camp/2011/Fahrplan/events/4553.en.html', 4553),
(131, 1312975800, 1312979400, 11, 'OpenLeaks- where leaking meets engineering', 'https://events.ccc.de/camp/2011/Fahrplan/events/4552.en.html', 4552),
(132, 1313154000, 1313157600, 11, 'Open-source 4G radio- It''s time to start WiMAX and LTE hacking', 'https://events.ccc.de/camp/2011/Fahrplan/events/4446.en.html', 4446),
(133, 1313245800, 1313247600, 10, 'Open source photovoltaics- power for off-grid devices', 'https://events.ccc.de/camp/2011/Fahrplan/events/4476.en.html', 4476),
(134, 1313240400, 1313244000, 11, 'Poker bots- Developing and running autonomous pokerbots at online casinos', 'https://events.ccc.de/camp/2011/Fahrplan/events/4424.en.html', 4424),
(135, 1313229600, 1313231400, 10, 'Post-Privacy und darüber hinaus- Was, wenn wir alle nackt wären?', 'https://events.ccc.de/camp/2011/Fahrplan/events/4461.en.html', 4461),
(136, 1313267400, 1313271000, 11, 'Rethinking online news- Journalism needs hackers to survive', 'https://events.ccc.de/camp/2011/Fahrplan/events/4491.en.html', 4491),
(137, 1313262000, 1313265600, 11, 'Reviving smart card analysis', 'https://events.ccc.de/camp/2011/Fahrplan/events/4500.en.html', 4500),
(138, 1312975800, 1312981200, 10, 'Rocket propulsion basics- An introduction to rocket engines and their application for space travel', 'https://events.ccc.de/camp/2011/Fahrplan/events/4436.en.html', 4436),
(139, 1313143200, 1313146800, 11, 'Runtime Reconfigurable Processors', 'https://events.ccc.de/camp/2011/Fahrplan/events/4399.en.html', 4399),
(140, 1313062200, 1313065800, 10, 'Solid rocket engines- Design and implementation of engines with solid propellant', 'https://events.ccc.de/camp/2011/Fahrplan/events/4440.en.html', 4440),
(141, 1313326800, 1313330400, 10, 'Space Debris- Simulation of orbital debris and its impacts on space travel', 'https://events.ccc.de/camp/2011/Fahrplan/events/4411.en.html', 4411),
(142, 1312992000, 1312995600, 10, 'Space Federation- Linking and Launching Earth-Based Hacker Spaces', 'https://events.ccc.de/camp/2011/Fahrplan/events/4493.en.html', 4493),
(143, 1313143200, 1313146800, 10, 'Sport für Nerds', 'https://events.ccc.de/camp/2011/Fahrplan/events/4549.en.html', 4549),
(144, 1313186400, 1313190000, 10, 'Stalker - Die strahlende Reise der Gebrüder Strugazki- Ein audiovisuelles Live-Hörspiel', 'https://events.ccc.de/camp/2011/Fahrplan/events/4492.en.html', 4492),
(145, 1313154000, 1313157600, 10, 'Strahlung im Weltall- Hell yeah, it''s radiation science!', 'https://events.ccc.de/camp/2011/Fahrplan/events/4505.en.html', 4505),
(146, 1312981200, 1312984800, 11, 'Strong encryption of credit card information- Attacks on common failures when encrypting credit card information', 'https://events.ccc.de/camp/2011/Fahrplan/events/4421.en.html', 4421),
(147, 1312992000, 1312995600, 11, 'Stuff you don''t see - every day- GNU Radio Internals - how to use the Framework', 'https://events.ccc.de/camp/2011/Fahrplan/events/4453.en.html', 4453),
(148, 1313235000, 1313238600, 10, 'Telemetry - Real-time communication during rocket flight', 'https://events.ccc.de/camp/2011/Fahrplan/events/4466.en.html', 4466),
(149, 1313094600, 1313098200, 10, 'Tempo/Rhythm/Echo extraction from Music', 'https://events.ccc.de/camp/2011/Fahrplan/events/4402.en.html', 4402),
(150, 1313321400, 1313325000, 10, 'The "Arguna" rocket family- An overview of our recent sounding rocket campaigns', 'https://events.ccc.de/camp/2011/Fahrplan/events/4455.en.html', 4455),
(151, 1313002800, 1313006400, 11, 'The blackbox in your phone- Some details about SIM cards', 'https://events.ccc.de/camp/2011/Fahrplan/events/4427.en.html', 4427),
(152, 1313256600, 1313260200, 10, 'The Joy of Intellectual Vampirism- Mindfucking with Shared Information', 'https://events.ccc.de/camp/2011/Fahrplan/events/4428.en.html', 4428),
(153, 1313251200, 1313254800, 10, 'There''s Gold in Them Circuit Boards- Why E-Waste Recycling Is Smart and How To Make It Smarter', 'https://events.ccc.de/camp/2011/Fahrplan/events/4443.en.html', 4443),
(154, 1312997400, 1313001000, 11, 'Transition Telecom- Telecommunications and networking during energy descent', 'https://events.ccc.de/camp/2011/Fahrplan/events/4459.en.html', 4459),
(155, 1313100000, 1313101800, 10, 'Who''s snitching my milk?- Nonlinear dynamics/analysis of vanishing bovine products in an office environment.', 'https://events.ccc.de/camp/2011/Fahrplan/events/4471.en.html', 4471),
(156, 1313332200, 1313335800, 10, 'Wie finanziere ich eine Mondmission? (Funtalk)- Von Würstchen verkaufen bis Ballonflüge - ein Erfahrungsbericht.', 'https://events.ccc.de/camp/2011/Fahrplan/events/4506.en.html', 4506),
(157, 1313164800, 1313168400, 10, 'Windkraftanlagen- Aufbau, Betrieb, Probleme', 'https://events.ccc.de/camp/2011/Fahrplan/events/4435.en.html', 4435);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Sprache`
--

CREATE TABLE IF NOT EXISTS `Sprache` (
  `TextID` varchar(35) NOT NULL DEFAULT 'makeuser_',
  `Sprache` char(2) NOT NULL DEFAULT 'DE',
  `Text` text NOT NULL,
  KEY `TextID` (`TextID`,`Sprache`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `Sprache`
--

INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) VALUES
('Hallo', 'DE', 'Hallo '),
('Hallo', 'EN', 'Greetings '),
('2', 'DE', ',\r\n\r\nIm Engelsystem eingeloggt..\r\nW&auml;hle zum Abmelden bitte immer den Abmelden-Button auf der linken Seite.'),
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
('back', 'DE', 'zur&uuml;ck '),
('back', 'EN', 'back '),
('top', 'DE', 'top'),
('top', 'EN', 'top '),
('13', 'DE', 'auf dieser Seite kannst Du deine pers&ouml;nlichen Einstellungen &auml;ndern, wie zum Beispiel dein Kennwort, Farbeinstellungen usw.\r\n\r\n'),
('13', 'EN', 'here you can change your personal settings i.e. password, color settings etc.\r\n\r\n'),
('14', 'DE', 'Hier kannst du dein Kennwort &auml;ndern.. '),
('14', 'EN', 'Here you can change your password.'),
('15', 'DE', 'Altes Passwort:'),
('15', 'EN', 'Old password:'),
('16', 'DE', 'Neues Passwort:'),
('16', 'EN', 'New password:'),
('17', 'DE', 'Passwortbest&auml;tigung:'),
('17', 'EN', 'password confirmation:'),
('18', 'DE', 'Hier kannst du dir dein Farblayout aussuchen:'),
('18', 'EN', 'Here you can choose your color settings:'),
('19', 'DE', 'Farblayout:'),
('19', 'EN', 'color settings:'),
('20', 'DE', 'Hier kannst Du dir deine Sprache aussuchen:\r\nHere you can choose your language:'),
('20', 'EN', 'Here you can choose your language:\r\nHier kannst Du dir deine Sprache aussuchen:'),
('21', 'DE', 'Sprache:'),
('21', 'EN', 'Language:'),
('22', 'DE', 'Hier kannst du dir einen Avatar aussuchen. Dies l&auml;sst neben deinem Nick z. B. in den News das Bildchen erscheinen.'),
('22', 'EN', 'Here you can choose your avatar. It will be displayed next to your Nick. '),
('23', 'DE', 'Avatar:'),
('23', 'EN', 'Avatar:'),
('24', 'DE', 'Keiner'),
('24', 'EN', 'nobody'),
('25', 'DE', 'Eingegebene Kennw&ouml;rter sind nicht gleich -&gt; OK.\r\nCheck ob altes Passwort ok ist:'),
('25', 'EN', 'The passwords entered don&#039;t match. -&gt; OK.\r\nCheck if the old password is correct:'),
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
('31', 'DE', 'Kennw&ouml;rter sind nicht gleich. Bitte wiederholen.'),
('31', 'EN', 'The passwords don&#039;t match. Please try again.'),
('32', 'DE', 'Neues Farblayout wurde gesetzt. Mit der n&auml;chsten Seite wird es aktiv.'),
('32', 'EN', 'New color settings are saved. On the next page it will be active.'),
('33', 'DE', 'Sprache wurde gesetzt. Mit der n&auml;chsten Seite wird es aktiv.'),
('33', 'EN', 'Language is saved. On the next page it will be active.'),
('34', 'DE', 'Avatar wurde gesetzt.'),
('34', 'EN', 'Avatar is saved.'),
('35', 'DE', '&lt;b&gt;Neue Anfrage:&lt;/b&gt;\r\nIn diesem Formular hast du die M&ouml;glichkeit, den Dispatchern eine Frage zu stellen. Wenn diese beantwortet ist, wirst du hier dar&uuml;ber informiert. Sollte die Frage von allgemeinem Interesse sein, wird diese in die FAQ &uuml;bernommen.'),
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
('pub_index_User_more_as_one', 'DE', 'F&uuml;r deinen Nick gab es mehrere User... bitte wende dich an die Dispatcher'),
('Hello', 'DE', 'Hallo '),
('Hello', 'EN', 'Hello '),
('pub_schicht_beschreibung', 'DE', 'Hier kannst du dich f&uuml;r Schichten eintragen. Dazu such dir eine freie Schicht und klicke auf den Link! Du kannst dir eine Schicht &uuml;ber den Raum bzw. Datum aussuchen. W&auml;hle hierf&uuml;r einen Tag / ein Datum aus.'),
('pub_schicht_auswahl_raeume', 'DE', 'Zur Auswahl stehende R&auml;ume:'),
('pub_schicht_alles_1', 'DE', 'Und nat&uuml;rlich kannst du dir auch '),
('pub_schicht_alles_2', 'DE', 'alles '),
('pub_schicht_alles_3', 'DE', 'auf einmal anzeigen lassen.'),
('pub_schicht_Anzeige_1', 'DE', 'Anzeige des Schichtplans am '),
('pub_schicht_Anzeige_2', 'DE', ' im Raum: '),
('pub_schicht_Anzeige_3', 'DE', 'Anzeige des Schichtplans f&uuml;r den '),
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
('pub_mywake_beschreibung1', 'DE', 'Hier siehst du die Schichten, f&uuml;r die du dich eingetragen hast.'),
('pub_mywake_beschreibung2', 'DE', 'Bitte versuche p&uuml;nktlich zu den Schichten zu erscheinen.'),
('pub_mywake_beschreibung3', 'DE', 'Hier hast du auch die M&ouml;glichkeit, dich bis '),
('pub_mywake_beschreibung4', 'DE', ' Stunden vor Schichtbeginn auszutragen.'),
('pub_mywake_anzahl1', 'DE', 'Du hast dich f&uuml;r '),
('pub_mywake_anzahl2', 'DE', ' Schichten eingetragen'),
('pub_mywake_Datum', 'DE', 'Datum'),
('pub_mywake_Uhrzeit', 'DE', 'Uhrzeit'),
('pub_mywake_Ort', 'DE', 'Ort'),
('pub_mywake_Bemerkung', 'DE', 'Bemerkung'),
('pub_mywake_austragen', 'DE', 'austragen'),
('pub_mywake_delate1', 'DE', 'Schicht wird ausgetragen...'),
('pub_mywake_add_ok', 'DE', 'Schicht wurde ausgetragen.'),
('pub_mywake_add_ko', 'DE', 'Sorry, ein kleiner Fehler ist aufgetreten... probiere es doch bitte nocheinmal :)'),
('pub_mywake_after', 'DE', 'zu sp&auml;t'),
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
('pub_wake_beschreibung', 'EN', 'Here you can register for a wake-up "call".  Simply say when and where the drone should come to wake you.\r\n'),
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
('pub_wake_change', 'DE', 'l&ouml;schen'),
('pub_wake_del', 'DE', 'l&ouml;schen'),
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
('pub_mywake_austragen_n_c', 'DE', 'nicht mehr m&ouml;glich'),
('pub_mywake_delate1', 'EN', 'Shift is being removed...'),
('pub_mywake_add_ok', 'EN', 'Shift has been removed.'),
('pub_mywake_add_ko', 'EN', 'Sorry, something went wrong somewhere.  Please try it again. :)\r\n'),
('pub_mywake_after', 'EN', 'sorry, too late!'),
('index_text1', 'DE', 'Wiederstand ist zwecklos!'),
('index_text2', 'DE', 'Deine physikalischen und biologischen Eigenschaften werden den unsrigen hinzugefuegt!'),
('index_text1', 'EN', 'Resistance is futile!\r\n'),
('index_text3', 'DE', 'Datenerfassungsbogen:'),
('index_text2', 'EN', 'Your biological and physical parameters will be added to our collectiv!'),
('index_text4', 'EN', 'Please note: You have to activate cookies!'),
('index_text4', 'DE', 'Achtung: Cookies m&uuml;ssen aktiviert sein'),
('index_text3', 'EN', 'Assimilating drone:'),
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
('pub_menu_menuname', 'DE', 'Men&uuml;'),
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
('pub_menu_Engelbesprechung', 'EN', 'Drone meeting'),
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
('pub_waeckliste_Text1', 'DE', 'dies ist die Weckliste. Schau hier bitte, wann die Leute geweckt werden wollen und erledige dies... schliesslich willst du bestimmt nicht deren Schichten uebernehmen :-)\r\n&lt;br&gt;&lt;br&gt;\r\nDie bisherigen eingetragenen Zeiten:'),
('pub_waeckliste_Nick', 'DE', 'Nick'),
('pub_waeckliste_Nick', 'EN', 'Nick'),
('pub_waeckliste_Datum', 'DE', 'Datum'),
('pub_waeckliste_Datum', 'EN', 'Date'),
('pub_waeckliste_Ort', 'DE', 'Ort'),
('pub_waeckliste_Ort', 'EN', 'Place'),
('pub_waeckliste_Comment', 'DE', 'Bemerkung'),
('pub_waeckliste_Comment', 'EN', 'comment'),
('pub_waeckliste_Text1', 'EN', 'This is the wake-up list. Pleace look here, when the drones  want to wake-up and \r\nhandle this... you don&#039;t want to take on this shift, isn&#039;t it?:-)\r\n&lt;br&gt;&lt;br&gt;\r\nShow all entries:'),
('pub_schichtplan_add_ToManyYousers', 'DE', 'FEHLER: Es wurden keine weiteren Engel ben&ouml;tigt !!'),
('pub_schichtplan_add_ToManyYousers', 'EN', 'ERROR: There are enough drones for this shift'),
('pub_mywake_Len', 'DE', 'L&auml;nge'),
('pub_mywake_Len', 'EN', 'length'),
('pub_schichtplan_add_AllreadyinShift', 'DE', 'du bist bereits in einer Schicht eingetragen!'),
('pub_schichtplan_add_AllreadyinShift', 'EN', 'you have another shift on this time'),
('pub_schichtplan_add_Error', 'DE', 'Ein Fehler ist aufgetreten'),
('pub_schichtplan_add_WriteOK', 'DE', 'Du bist jetzt der Schicht zugeteilt. Vielen Dank f&uuml;r deine Mitarbeit.'),
('pub_schichtplan_add_Text1', 'DE', 'Hier kannst du dich in eine Schicht eintragen. Als Kommentar kannst du etwas x-beliebiges eintragen, wie z. B.\r\nwelcher Vortrag dies ist oder &auml;hnliches. Den Kommentar kannst nur du sehen. '),
('pub_schichtplan_add_Date', 'DE', 'Datum'),
('pub_schichtplan_add_Place', 'DE', 'Ort'),
('pub_schichtplan_add_Job', 'DE', 'Aufgabe'),
('pub_schichtplan_add_Len', 'DE', 'Dauer'),
('pub_schichtplan_add_TextFor', 'DE', 'Text zur Schicht'),
('pub_schichtplan_add_Comment', 'DE', 'Dein Kommentar'),
('pub_schichtplan_add_submit', 'DE', 'Ja, ich will helfen...&quot;'),
('index_text5', 'DE', 'Bitte &uuml;berpr&uuml;fen Sie den SSL Key'),
('index_text5', 'EN', 'Please check your SSL-Key:'),
('pub_myshift_Edit_Text1', 'DE', 'Hier k&ouml;nnt ihr euren Kommentar &auml;ndern:'),
('pub_myshift_EditSave_Text1', 'DE', 'Text wird gespeichert'),
('pub_myshift_EditSave_OK', 'DE', 'erfolgreich gespeichert.'),
('pub_myshift_EditSave_KO', 'DE', 'Fehler beim Speichern'),
('pub_sprache_text1', 'DE', 'hier kannst du die &uuml;bersetzten Texte bearbeiten.'),
('pub_sprache_text1', 'EN', 'here can you edit the texts of the dronesystem'),
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
('pub_aktive_Text1', 'DE', 'Diese Funktion erm&ouml;glicht es den Dispatchern, schnell einen Engel mit einer vorgebbaren Anzahl an Stunden als Aktiv zu markieren.'),
('pub_aktive_Text1', 'EN', 'This function enables the archdrones to mark drones as active who worked enough hours.'),
('pub_aktive_Text2', 'DE', '&Uuml;ber die Engelliste kann dies f&uuml;r einzelne Drohne erledigt werden.'),
('pub_aktive_Text2', 'EN', 'Over the dronelist you can do this for single drones.'),
('pub_aktive_Text31', 'DE', 'Alle Engel mit mindestens'),
('pub_aktive_Text31', 'EN', 'All drones with at least'),
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
('pub_aktive_Text5_1', 'EN', 'All drones with at least '),
('pub_aktive_Text5_2', 'DE', ' Schichten werden jetzt als &quot;Aktiv&quot; markiert'),
('pub_aktive_Text5_2', 'EN', ' shifts were marked as &quot;active&quot;'),
('pub_aktive_Active', 'DE', 'Aktiv'),
('pub_aktive_Active', 'EN', 'active'),
('pub_schichtplan_add_TextFor', 'EN', 'text for shift'),
('pub_schichtplan_add_WriteOK', 'EN', 'Now, you signed up for this shift. Thank you for your cooperation.'),
('pub_schichtplan_add_Text1', 'EN', 'Here you can sign up for a shift. As commend can you write what you want, it is only for you.'),
('pub_schichtplan_colision', 'DE', '&lt;h1&gt;Fehler&lt;/h1&gt;\r\n&Uuml;berschneidung von Schichten:'),
('pub_schichtplan_colision', 'EN', '&lt;h1&gt;error&lt;/h1&gt;\r\noverlap on shift:'),
('pub_schicht_EmptyShifts', 'DE', 'Die n&auml;chsten 15 freien Schichten:'),
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
('pub_einstellungen_UserDateSaved', 'DE', 'Deine Beschreibung f&uuml;r unsere Dronenverwaltung wurde ge&auml;ndert.'),
('pub_einstellungen_UserDateSaved', 'EN', 'Your user details were saved.'),
('pub_menu_SchichtplanBeamer', 'DE', 'Schichtplan f&uuml;r Beamer optimiert'),
('pub_menu_SchichtplanBeamer', 'EN', 'Shifts for beamer optimice'),
('pub_einstellungen_Text_UserData', 'DE', 'Hier kannst du deine Beschreibung f&uuml;r unsere Engelverwaltung &auml;ndern.'),
('lageplan_text1', 'EN', 'This is a map of available rooms:'),
('register', 'DE', 'Engel werden'),
('register', 'EN', 'Become an angel'),
('makeuser_text1', 'DE', 'Mit dieser Maske meldet ihr euch im Engelsystem an. Durch das Engelsystem findet auf der Veranstaltung die Aufgabenverteilung der Engel statt.\r\n\r\n'),
('makeuser_text1', 'EN', 'By completing this form you&#039;re registering as a Chaos-Drone. This script will create you an account in the drone task sheduler.\r\n\r\n'),
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
('makeuser_T-Shirt', 'DE', 'T-Shirt Gr&ouml;&szlig;e'),
('makeuser_T-Shirt', 'EN', 'shirt size'),
('makeuser_Engelart', 'DE', 'Zuteilung'),
('makeuser_Engelart', 'EN', 'designation'),
('makeuser_Passwort', 'DE', 'Passwort'),
('makeuser_Passwort', 'EN', 'password'),
('makeuser_Passwort2', 'DE', 'Passwort Best&auml;tigung'),
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
('makeuser_error_password1', 'DE', 'Fehler: Passw&ouml;rter sind nicht identisch.'),
('makeuser_error_password1', 'EN', 'error: your passwords don&#039;t match'),
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
('makeuser_writeOK4', 'EN', 'Drone registered!'),
('makeuser_text4', 'DE', 'Wenn du dich als Engel registrieren  m&ouml;chtest, f&uuml;lle bitte folgendes Formular aus:'),
('makeuser_text4', 'EN', 'If you would like to be a chaos drone please insert following details into this form:'),
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
('pub_messages_An', 'DE', 'Empf&auml;nger'),
('pub_messages_An', 'EN', 'receiver'),
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
('pub_messages_text1', 'EN', 'here can you leave messages for other drones'),
('pub_messages_DelMsg', 'DE', 'Nachricht l&ouml;schen'),
('pub_messages_DelMsg', 'EN', 'delete message'),
('pub_messages_DelMsg_OK', 'DE', 'Nachricht gel&ouml;scht'),
('pub_messages_DelMsg_OK', 'EN', 'delete message'),
('pub_messages_DelMsg_KO', 'DE', 'Nachricht konnte nicht gel&ouml;scht werden'),
('pub_messages_DelMsg_KO', 'EN', 'cannot delete message'),
('pub_messages_new1', 'DE', 'Du hast'),
('pub_messages_new1', 'EN', 'You have'),
('pub_messages_new2', 'DE', 'neue Nachrichten'),
('pub_messages_new2', 'EN', 'new messages'),
('pub_messages_NotRead', 'DE', 'nicht gelesen'),
('pub_messages_NotRead', 'EN', 'not read'),
('pub_mywake_Name', 'DE', 'Schicht Titel'),
('pub_mywake_Name', 'EN', 'shift title'),
('pub_sprache_ShowEntry', 'DE', 'Eintr&auml;ge anzeigen'),
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
('pub_menu_Engelliste', 'EN', 'Drone-list'),
('pub_menu_EngelDefaultSetting', 'DE', 'Engel Voreinstellungen'),
('pub_menu_EngelDefaultSetting', 'EN', 'Drone Default Setting'),
('pub_menu_Aktivliste', 'DE', 'Aktiv Liste'),
('pub_menu_Aktivliste', 'EN', 'active list'),
('pub_menu_T-Shirtausgabe', 'DE', 'T-Shirtausgabe'),
('pub_menu_T-Shirtausgabe', 'EN', 'T-Shirt handout'),
('pub_menu_News-Verwaltung', 'DE', 'News-Verwaltung'),
('pub_menu_News-Verwaltung', 'EN', 'News-Center'),
('faq', 'DE', 'FAQ'),
('faq', 'EN', 'FAQ'),
('pub_menu_FreeEngel', 'DE', 'Freie Engel'),
('pub_menu_FreeEngel', 'EN', 'free Drones'),
('pub_menu_Debug', 'DE', 'Debug'),
('pub_menu_Debug', 'EN', 'Debug'),
('pub_menu_Recentchanges', 'DE', 'Letzte &Auml;nderungen'),
('pub_menu_Recentchanges', 'EN', 'recent changes'),
('pub_menu_Language', 'DE', 'Sprachen'),
('pub_menu_Language', 'EN', 'Language'),
('makeuser_text0', 'DE', 'Anmeldung als Engel'),
('makeuser_text0', 'EN', 'Drone registration'),
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
('nonpublic/engelbesprechung.php', 'EN', 'Drone gathering'),
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
('nonpublic/schichtplan_beamer.php', 'DE', 'Schichtplan f&uuml;r Beamer optimiert'),
('nonpublic/schichtplan_beamer.php', 'EN', 'Shifts for beamer optimice'),
('nonpublic/faq.php', 'DE', 'Anfragen an die Dispatcher'),
('nonpublic/faq.php', 'EN', 'Questions for the Dispatcher'),
('admin/index.php', 'DE', ' '),
('admin/index.php', 'EN', ' '),
('pub_einstellungen_PictureUpload', 'DE', 'Hochzuladendes Bild ausw&auml;hlen:'),
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
('delete', 'DE', 'l?schen'),
('delete', 'EN', 'delete'),
('upload', 'EN', 'upload'),
('upload', 'DE', 'hochladen'),
('pub_einstellungen_PictureNoShow', 'DE', 'Das Foto ist nicht freigegeben'),
('pub_einstellungen_send_KO', 'DE', 'Beim Hochladen ist ein Fehler aufgetreten.'),
('pub_einstellungen_send_KO', 'EN', 'An error was detected. Please try again!'),
('admin/room.php', 'DE', 'Räume'),
('admin/room.php', 'EN', 'rooms'),
('admin/EngelType.php', 'DE', 'Engeltypen'),
('admin/EngelType.php', 'EN', 'Drone-Types'),
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
('admin/userDefaultSetting.php', 'EN', 'Drone Default Setting'),
('admin/UserPicture.php', 'DE', 'Benutzerbilder'),
('admin/UserPicture.php', 'EN', 'User Pictures'),
('admin/aktiv.php', 'DE', 'Aktiv Liste'),
('admin/aktiv.php', 'EN', 'active list'),
('admin/tshirt.php', 'DE', 'T-Shirtausgabe'),
('admin/tshirt.php', 'EN', 'T-Shirt handout'),
('admin/news.php', 'DE', 'News-Verwaltung'),
('admin/news.php', 'EN', 'News-Center'),
('admin/free.php', 'DE', 'Freie Engel'),
('admin/free.php', 'EN', 'free Drones'),
('admin/debug.php', 'DE', 'Debug'),
('admin/debug.php', 'EN', 'Debug'),
('admin/Recentchanges.php', 'DE', 'Letzte ?nderungen'),
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
('admin_shifts', 'EN', 'Create shifts');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `UID` int(11) NOT NULL AUTO_INCREMENT,
  `Nick` varchar(23) NOT NULL DEFAULT '',
  `Name` varchar(23) DEFAULT NULL,
  `Vorname` varchar(23) DEFAULT NULL,
  `Alter` int(4) DEFAULT NULL,
  `Telefon` varchar(40) DEFAULT NULL,
  `DECT` varchar(4) DEFAULT NULL,
  `Handy` varchar(40) DEFAULT NULL,
  `email` varchar(123) DEFAULT NULL,
  `ICQ` varchar(30) DEFAULT NULL,
  `jabber` varchar(200) DEFAULT NULL,
  `Size` varchar(4) DEFAULT NULL,
  `Passwort` varchar(40) DEFAULT NULL,
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
  PRIMARY KEY (`UID`,`Nick`),
  UNIQUE KEY `Nick` (`Nick`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=149 ;

--
-- Daten für Tabelle `User`
--

INSERT INTO `User` (`UID`, `Nick`, `Name`, `Vorname`, `Alter`, `Telefon`, `DECT`, `Handy`, `email`, `ICQ`, `jabber`, `Size`, `Passwort`, `Gekommen`, `Aktiv`, `Tshirt`, `color`, `Sprache`, `Avatar`, `Menu`, `lastLogIn`, `CreateDate`, `Art`, `kommentar`, `Hometown`) VALUES
(1, 'admin', 'Gates', 'Bill', 42, '', '', '', '', '', '', '', '21232f297a57a5a743894a0e4a801fc3', 1, 1, 0, 10, 'DE', 115, 'L', 1315430361, '0000-00-00 00:00:00', '', '', ''),
(148, 'msquare', '', '', 23, '', '', '', 'msquare@notrademark.de', '', '', '', '4297f44b13955235245b2497399d7a93', 0, 1, 1, 10, 'DE', 0, 'L', 1307110798, '2011-06-03 07:55:24', 'AudioEngel', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserCVS`
--

CREATE TABLE IF NOT EXISTS `UserCVS` (
  `UID` int(11) NOT NULL DEFAULT '0',
  `GroupID` int(11) DEFAULT '-2',
  `index.php` char(1) NOT NULL DEFAULT 'G',
  `logout.php` char(1) NOT NULL DEFAULT 'G',
  `faq.php` char(1) NOT NULL DEFAULT 'G',
  `lageplan.php` char(1) NOT NULL DEFAULT 'G',
  `makeuser.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/index.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/news.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/newsAddMeting` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/news_comments.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/myschichtplan.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/myschichtplan_ical.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/schichtplan_beamer.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/engelbesprechung.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/schichtplan.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/schichtplan_add.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/wecken.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/waeckliste.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/messages.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/faq.php` char(1) NOT NULL DEFAULT 'G',
  `nonpublic/einstellungen.php` char(1) NOT NULL DEFAULT 'G',
  `Change T_Shirt Size` char(1) NOT NULL DEFAULT 'G',
  `admin/index.php` char(1) NOT NULL DEFAULT 'G',
  `admin/room.php` char(1) NOT NULL DEFAULT 'G',
  `admin/EngelType.php` char(1) NOT NULL DEFAULT 'G',
  `admin/schichtplan.php` char(1) NOT NULL DEFAULT 'G',
  `admin/shiftadd.php` char(1) NOT NULL DEFAULT 'G',
  `admin/schichtplan_druck.php` char(1) NOT NULL DEFAULT 'G',
  `admin/user.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userChangeNormal.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userSaveNormal.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userChangeSecure.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userSaveSecure.php` char(1) NOT NULL DEFAULT 'G',
  `admin/group.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userDefaultSetting.php` char(1) NOT NULL DEFAULT 'G',
  `admin/UserPicture.php` char(1) NOT NULL DEFAULT 'G',
  `admin/userArrived.php` char(1) NOT NULL DEFAULT 'G',
  `admin/aktiv.php` char(1) NOT NULL DEFAULT 'G',
  `admin/tshirt.php` char(1) NOT NULL DEFAULT 'G',
  `admin/news.php` char(1) NOT NULL DEFAULT 'G',
  `admin/faq.php` char(1) NOT NULL DEFAULT 'G',
  `admin/free.php` char(1) NOT NULL DEFAULT 'G',
  `admin/sprache.php` char(1) NOT NULL DEFAULT 'G',
  `admin/dect.php` char(1) NOT NULL DEFAULT 'G',
  `admin/dect_call.php` char(1) NOT NULL DEFAULT 'G',
  `admin/dbUpdateFromXLS.php` char(1) NOT NULL DEFAULT 'G',
  `admin/Recentchanges.php` char(1) NOT NULL DEFAULT 'G',
  `admin/debug.php` char(1) NOT NULL DEFAULT 'G',
  `Herald` char(1) NOT NULL DEFAULT 'G',
  `Info` char(1) NOT NULL DEFAULT 'G',
  `Conference` char(1) NOT NULL DEFAULT 'G',
  `Kasse` char(1) NOT NULL DEFAULT 'G',
  `credits.php` char(1) NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `UserCVS`
--

INSERT INTO `UserCVS` (`UID`, `GroupID`, `index.php`, `logout.php`, `faq.php`, `lageplan.php`, `makeuser.php`, `nonpublic/index.php`, `nonpublic/news.php`, `nonpublic/newsAddMeting`, `nonpublic/news_comments.php`, `nonpublic/myschichtplan.php`, `nonpublic/myschichtplan_ical.php`, `nonpublic/schichtplan_beamer.php`, `nonpublic/engelbesprechung.php`, `nonpublic/schichtplan.php`, `nonpublic/schichtplan_add.php`, `nonpublic/wecken.php`, `nonpublic/waeckliste.php`, `nonpublic/messages.php`, `nonpublic/faq.php`, `nonpublic/einstellungen.php`, `Change T_Shirt Size`, `admin/index.php`, `admin/room.php`, `admin/EngelType.php`, `admin/schichtplan.php`, `admin/shiftadd.php`, `admin/schichtplan_druck.php`, `admin/user.php`, `admin/userChangeNormal.php`, `admin/userSaveNormal.php`, `admin/userChangeSecure.php`, `admin/userSaveSecure.php`, `admin/group.php`, `admin/userDefaultSetting.php`, `admin/UserPicture.php`, `admin/userArrived.php`, `admin/aktiv.php`, `admin/tshirt.php`, `admin/news.php`, `admin/faq.php`, `admin/free.php`, `admin/sprache.php`, `admin/dect.php`, `admin/dect_call.php`, `admin/dbUpdateFromXLS.php`, `admin/Recentchanges.php`, `admin/debug.php`, `Herald`, `Info`, `Conference`, `Kasse`, `credits.php`) VALUES
(1, -4, 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G'),
(-1, NULL, 'Y', 'N', 'Y', 'N', 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'Y'),
(-2, NULL, 'N', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'Y'),
(-3, NULL, 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y'),
(-4, NULL, 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y'),
(-5, NULL, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserGroups`
--

CREATE TABLE IF NOT EXISTS `UserGroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Daten für Tabelle `UserGroups`
--

INSERT INTO `UserGroups` (`id`, `uid`, `group_id`) VALUES
(1, 1, -2),
(2, 1, -3),
(3, 1, -6),
(4, 1, -5),
(11, 148, -2),
(12, 1, -4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserPicture`
--

CREATE TABLE IF NOT EXISTS `UserPicture` (
  `UID` int(11) NOT NULL DEFAULT '0',
  `Bild` longblob NOT NULL,
  `ContentType` varchar(20) NOT NULL DEFAULT '',
  `show` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `UserPicture`
--

INSERT INTO `UserPicture` (`UID`, `Bild`, `ContentType`, `show`) VALUES
(-1, 0xffd8ffe000104a46494600010200000100010000ffe100e645786966000049492a00080000000500120103000100000001000000310102001c0000004a00000032010200140000006600000013020300010000000100000069870400010000007a000000000000004143442053797374656d73204469676974616c20496d6167696e6700323030363a31323a31352031353a34303a353700050000900700040000003032323090920200040000003632320002a00400010000007401000003a00400010000002c01000005a0040001000000bc0000000000000002000100020004000000523938000200070004000000303130300000000000000000ffc0001108012c017403012100021101031101ffdb008400010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101ffc401a20000010501010101010100000000000000000102030405060708090a0b100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9fa0100030101010101010101010000000000000102030405060708090a0b1100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fefe28a0028a0028a0028a0028a0028a0028a0028a002bc33f68bfda6bf67efd91fe16ebbf1abf698f8bfe04f829f0bfc3d15d1bcf1678f75eb5d1adafafedb49d535c8fc39e1ab0776d5fc63e33d534dd175497c3be06f0969fae78c7c517167258787342d575031dab9fd7f5bfe5f78d26dd92bbfe9b7d6c96eddac92bbb9fc03ffc1463fe0f35f8d5e3bbcd5be1f7fc1343e155afc0ef06ab0807ed07f1d345f0ff008dfe336b051bc19a9c379e0ff855e76bdf09be1c436fa85878e7c2da92f8d67f8e3378bfc27ade89e20d3ad7e15f8a6c25b283f956fda0bfe0a67ff050afdaa2e35d6fda03f6d1fda4fe25e91e21f165ef8def3c15ac7c5df19da7c32b0f12df4ba9c86f7c35f0ab46d4f4cf86fe0fb6b18f58d4ec344d27c27e16d1748f0ee9177268da169fa7693b2c94df7ff81af975febb8d49c5a717669a6a69b524d6a9c5f2a6b5d53d1dd27eebf87cc7e17feda1fb617c11beb9d53e0bfed5dfb49fc23d4eef4b9b44bad4be197c73f89de02d427d1ae2f6cf51b8d264bdf0b789b4cba7d3a7d434eb0bd9ec8ca2de6bab3b59e48da482364fd91ff827e7fc1cefff000542fd893c516b6ff107e2eebdfb6bfc19bfd64ea3e2cf863fb5178afc43e39f17bc57fa8f851b5bbdf007c7bd5a4d5fe2b782f5ff00f847fc3573a1785ac75cd57c7df08fc3571e25d7bc4b71f07b5ed7ee85e22b24acb45d12d97e1fa7f9c473729394db9b936e4e526e4db7ab7269b726f5bb72f33fbc2fd803fe0e63ff00825d7edbfe0f8e5f187c68f0d7ec69f1734ad1a1bff18fc2dfdaa7c61e14f871a243736d61e135d6eefe1ffc65d6750d3be18f8fbc3bff00092f896eb42f0b5bdd6afe11f8ade21b1f0eeb1e28d4fe10f86345b73227f12bff0005d2ff0083853f68afdb53f6a7f1ff0081ff00636fda53e2d7c2efd86fc0965aa7c39f87767f0af5af1efc0fd47e3b691ae683a1d87c4af19fc5fb5d27c4b6dac78df43f15f88ecf5cb0f87da0f8b2cf42b2d27e15368d1ea7f0ff00c37e33f127c431aa9bdba757beb6e97b2eb67b6a959deef96aca29cb495e3686abed3d5b8f24efcb1528b5eeb8ca509dfe1e6fe73be1f7c6cf8cbf09be2658fc69f857f16be267c34f8c7a65deb97fa6fc58f0078f3c53e0ef897a7dff0089f4cd4f45f12ded8f8efc3da9e9fe28b4bcf1068fad6b1a4eb77506a71cdaae9bab6a5657cf716d7b711bfd6a9ff0566ff82a7a2ba8ff008295fedf789176b6ff00db17f686760339f919fe20bb46d9fe28cc6c7bb114ff00afebfad7a917fd77f3f54ff5d75df53ea1f829ff000711ff00c168fe017856ebc1de08fdbefe2d788746bcd76efc4535cfc6bd1fe1cfed21e2917f7b65a7584d6f6be3dfda13c0ff0013fc7761a1241a5dac969e17b0f125af86b4fbd9351d4ac749b6d4758d5aeaf7eccf81bff0772ffc165be146bdab6abf10be25fc15fda6b4bd4ec22b2b6f0cfc67f813e0bf0de95e1e992f21b99757d0af3f674b7f80fafc9aacb6f1c961b3c49ac788b464b79e495747fb6a437711fd7f5fd7e8175d52fbda7eb650b5faefafda7b33fb0cfd8d7fe0ed4ff82537ed35ff0008e786be2f789fc7ff00b197c4cd5ffe159e873693f1d3c3536abf0baffc75e3969b4ff10e9fe19f8d5f0e878a3c3ba5f807c01afc3041adfc51f8f1a47c01d14f87357d1fc5977a7693676de2fb4f07ff004f940bfafebfafbf4614500145001450014500145001450014500145001450014500145001450014500145001450014500145007e1b7fc15fbfe0bcffb25ff00c1267c1874bd56ef41fda07f6a8d4b52d2ad3c3ffb29f833e21e99a178bf4ed2ee9744d5b53f167c5ad7ecf42f1bcbf077c329e15d66df55f094de25f0addeb5f11352bab0b1f07e87a86896fe2ef15783bfcb03f6feff0082937ed77ff052df8c1a97c5efdaa3e286ade280bad6b7aa780be176937fad58fc1bf83563af5ae85a7df787be117802ff0056d56cbc23a5dce9de18f0ddaeaf7fe7dff89fc5d71a2da6b9e37f107897c4925deb374b7ff87ebf77eaf5d7b72d7c2bfbd2fc23ebde5bbdbddb6ea4f97e0fa29921450014500145001450015fe82fff0006fdff00c1cfff00087c35f0b7e08ffc13ff00fe0a11369ff0b67f875a57827e0bfc03fda86da3d0b42f8491fc36f0c786a6f0ff0083bc2dfb43493de69317c3abdf085a689e18f0768df14f4cb1d57c2be22d3353b5d4fe292f8065f09788be22f8d8febfab27fd77bde351b3bc5d95f66db8d9abe9a466ad2db58d93e56e518a933fbe1a282428a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a002bf832ff82e9ffc1d69e19d2340f881fb20ff00c12c3c5daa5efc415f126a5e0bf89bfb6ae91fd8573e09d27c356165a6ff006b597eca7aadbea1abcbe2bd775ed5ae759f09df7c6dd4749d2343f0be95a0dfebdf049bc6d79e31f047c61f01035d5f4fd5eda59fcfb5bed5d72ff9fc78c7c65e2ff889e2cf1278f7e2078abc49e3af1cf8cb5cd4fc4de2ff001a78c75cd53c4fe2cf15f8935bbc9750d67c41e24f11eb7757dac6bbae6af7f3cf7da9eadaa5e5d5fdfde4d2dcddcf2cd233b73740b7d5eaff00af5febbee1450014500145001450014500145007f735ff0006dfff00c1c936ff0005e0f0afec0bff00051bf8a0d1fc1e8db4bf0dfecd5fb4c78f351b99cfc2233dc5b69ba67c1ef8c5e29bbf3cc5f076269a15f01fc47f115cc769f05ed2297c35e2ed621f8370787ef3e107fa2f51fd7f5fd7e854acf55d56aaf7d7afdef5f9f5d185141214500145001450014500145001450014500145001450014500145001450014500145007f9a97fc1c1dff000731f8cff69793e367fc13f7f61f922f037eceba7f8d3c59f0d3e2efed27e16f1de9fe23d77f6a4f06e910dae85abf863e1e5ff85eddb45f077c07f146bd178a23d575bd03c59e2fd4be3dfc3bff00845229355f05f80bc43e3ff0078dbf8b4a3e56febfaff83a32a565eea7cc937aabd9bead5d2767a5aeb54aef576894504851400514005140051400514005140057f633ff000432ff0083a33e2bfec977df07ff00640fdbd35483e247ec73a52cde0bf0e7c71b9d3b5dd5be36fecf3a1c967a2587822c750b8d2a5bc1f13be08f81a4d32faca7f0e4fe1cbbf8a7e1ad07c4b2dcf857c5be20f0e7c3ef07fc1cbf3fafe975febcca8dbe17b49aeb6b3d936f95e8afef796babb1fe987e18f13f86bc6de1af0f78cfc19e21d0fc5de0ff0017687a4f89fc29e2bf0c6ad61aff0086bc4fe1ad7ec2df55d0bc43e1ed774ab8bbd2f5ad0f5ad2eeed752d2756d36eae6c352b0b9b7bcb3b89ade68e46dca09db47a3eddbfaf441450014500145001450014500145001450014500145001450014500145001450015fc2bffc1da5ff0005ac6f86fe1fd7ff00e094bfb347893c63a0fc52f1359787354fdb07e21787b533e1db3d03e16f8b3c3035fd1ff675b09d74f3afeaba87c55d075ff0d78bfe266a5a2eaba06876bf0d65d2be1cde5c78eec3e28fc43f0f7828febfad1ff5d761aeaf4d13d1b6b5d95ad7d537cdb5bddf7aeac7f9d7d140828a0028a0028a0028a0028a0028a0028a0028a00ff469ff008344ff00e0b0f77f16bc1337fc12dbf689f18ea9ab7c4af859a06abe2bfd92bc65e33f17e8f3c9e27f833e1eb2d221d5bf672d1ad7575b0f136abaf7c1eb58f52f1a7c3dd3ad352f1a5c7fc2971e27d02c74cf027807e01e8d0eabfdc7d1fd7f5fd3ff00e45bfcd79fa3dfcd5faaff00db4a2810514005140051400514005140051400514005140051400514005140051401fcff00ff00c1c43ff057af107fc1267f645f0beb1f07ec7c33abfed37fb45f8a75ff00875f0722f139bb9acbc09a3e87e18b8d4fc77f1b13443a0ea9a178ca7f8777fa9f81344d3bc1dafea7a2d85f7883c7da1eb97d1789340f0e6bfe18d53fc8bfc71e36f17fc4bf1a78bbe237c41f12eb3e32f1e78fbc4daf78d3c6be2ff11ea171aaf883c53e2cf146a973adf88bc45ae6a976f25d6a3abeb5abdf5dea5a95f5cbbcf7779712cf2b33b9653fafebf0efb6bd3969e915e7abd7cecb4eeacddefadd69a291cbd141214500145001450014500145001450014500145007a27c21f8b1f107e037c56f86bf1bbe13788a5f08fc50f843e3bf0a7c4bf877e288ac349d59bc3fe35f046bb61e24f0ceae748d7ec355d07588ac358d3acee67d235dd2b54d13548637b0d5f4dd434fb8b8b57ff670ff00823c7fc1493c37ff000552fd863e1dfed4763a5e8be16f8809aa6b5f0d3e3af80bc3d36b775a3780fe32783e3d3ae75cd274bbbd76c6d6e66d23c43e1ad77c23f11342b68eef5ffec5d03c6ba5f87b50f11eb3ae68fab5dd1fd7f5fd7dfbc5fd97abba7a2e9afc4fd748f6befaf2ae5fd44a281051400514005140051400514005140051400514005140051400514005717f123e21f82fe117c3bf1efc58f891afda7853e1dfc30f05f8a7e21f8f7c1400514005140051400514005140051400514005140057f089ff00079d7fc14574cd17e1c7c1cff826c7c2cf88ba5dc7897c6dae47f19bf6aaf0a68179ae7f6ce83e0cf0c0d2af3e057827c657367245e17974df1cf89ef358f89971e0cd44ea3e27d3ae7e197c32f19ddd8683a36bbe15d43c4c7f5faff5f8dca8f576d93f95f44fff000271ff0087e571ff003c2a282428a0028a0028a0028a0028a0028a0028a0028a0028a0028a00fef33fe0d31ff82dac1e12bef0a7fc1277f69cf13782bc39e05bd7f125cfec69f11bc4378de1fd447c42f16f8ca0d7aebf664be9ed7413a26b72fc40d77c4fe32f18fc2ef1078c3c43a2eb4be2e59fe0ee9179e32bff001bfc25f07f86ff00d0b28f2febfafbff002e5a96bef6bef5eef5b732b736ad2bbf864f4d2fd74614504851400514005140051400514005140051400514005140051401c0fc55f89fe05f825f0bfe247c67f8a1aeaf85be1a7c22f0178c3e27fc44f133e9faaeac9e1df02f807c3da8f8afc5baebe95a0d86a9ae6a6ba4681a4ea1a8369fa3699a8eab782dfecfa7585e5e4b0dbcbfe217ff000504fdb23c6fff000503fdb37f685fdb0bc7f66749d67e36f8faef5dd23c34d3e95787c13e00d16c6c3c25f0b7e1f36aba2f87bc2963e2093e1ffc34f0ff0084bc19378a5fc3da5ea5e2c9f4293c4fae4326b9ab6a37129fd7f5fd7dfbc5f47dff004ebf8db75afc91f1cd140828a0028a0028a0028a0028a0028a0028a0028a0028a0028a00b169757563756d7d63733d9ded9dc437567776b3496f756b756f22cd6f736d71132cb04f04c892c3344cb24522aba30650d5fea81ff06e1ffc17cbc07ff0501f853e05fd8d7f68ff00125b785bf6eaf84be08b1f0ee8d7fe24f116a9a87fc35c7827c0be1fd8ff0011bc3fadf8ab53d535bd63e39697e1cd1e5d73e38f846ff55d4b55f113da6b9f19fc1e66f0adc78dfc2ff08cfeb7fd2cefadbfcf7296a9abaeeba5dadd5f95f4bbb5fde692df959fd5851412145001450014500145001450014500145001450014500145007f0ddff07a57ede761e0cf825f007fe09d7e0bd7b5eb2f1d7c60f10d8fed1df19a0d13c43e23d06c57e0af82e5f14f847e1c7843c5ba441a2c5e1ff1ef87fe257c555d6bc636763278a267f07f88ff00678d1f55d5fc2d2cfaef8475cd3bfce628febfafebcf5dc6fa2f2fcf5f9f4bf55b3bd938945020a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a9609e6b59a1b9b69a5b7b9b79639edee2091e19a09a2712453432c644914b1c8aaf1c88cae8ea194ee00a81b6ab47d3faff0086fd4fedbbfe0dfeff0083987f6abd1fe3cfecf5fb03feda9a96b5fb4dfc36f8e1f15b49f84bf0f7e3bf8a759d67c41fb487807c7bf17bc4da2787fe1fd878c3c65e21d66787e2c7c3287c6da89b1be1e2944f88fe16d33c5b79a9e9be39f10f86bc0fe16f858ffe90b47f5fd7f4fbff0085bb68d75dd767df65a3d3e77d1251e628a04145001450014500145001450014500145001450015f24fedc1fb6efecf3ff0004f3fd9c3c75fb517ed33e2e7f0c7c3bf05411db59699a5c36fa8f8d7e21f8c7508ae0f867e1afc36f0f5c5e69ebe24f1df8aee2da6874bb09af74ed1f4bb1b6d53c55e2fd6fc35e08f0f7897c4fa29fd7dfff0006dea349bd12beef4ec95dbd3a25ab7d12bb3fc61ffe0a0bfb67fc41ff0082847ed91f1f3f6bcf890b7163abfc62f1d6a1ab787bc313dd6937e3e1ff00c38d2d21d03e177c348753d0fc37e10d3f5c8fe1e7c3dd2fc37e1093c4ede1bd2f54f17dce8f71e2cf11453f88b5bd5aee6f8d6807bf7ff2e9bebb5bf50a28105140051400514005140051400514005140051400514005140051401fb17ff06fcfc0fb2fda0ffe0b31ff0004fbf01dfebf3f86e0f0ff00c7383e3826a16f611ea525cdefeccbe14f137ed21a6e806da4bcb15483c57a97c29b4f0c5d5faccf26916babcdab4767a8c96296171fecdb47f5fd7f5fa0decbbeaefe5a2b5addd5f777f92e528a0414500145001450014500145001450014500145001f00b36fbc6bf117e376b5fb456a8d6de26b51acf857c23f087c0fe23f873a3c5abf83e2b59afce95f10f5cf8cdab4be1ff11dedf69b65f6cf859e24d374fb4d72e13509fc387f5dbfaefe76f9c6a3d77b28c9b69376d2caf66aca4da8dde8ae9da5a23fce328a090a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2803fa00ff835c149ff0082eb7ec3a411f2afed32c7dc1fd8ff00e3faf1ef9653f41df8dbfebf140df4f4fd7fa7d7ff009128a04145001450014500145001450014500145001450015fc2a7fc1e7bff0004f9f1dfc46f067ecfbff050cf86ba57897c5769f0834bbdf805f1d743d2adf55d662f0af80b57d5758f1bfc37f8989a6697e1db8b7d0fc3ba678ab50f19784be21789f5df1141036a7e2ef84da6e99a5e4eaf7b49f7eceffa3bd93e8dfa6ed9a534e4dc16f38b8dbab6ad28a5ddca518ab6eefeede5ca7f9d9514ccc28a0028a0028a0028a00f4ff027c19f89df129d3fe10ef07eadaa5a33aa36aaf10b0d16224f3e66af7e6df4fca8cb3451cef3607cb139da2bec0f0a7fc13ebc4f790a4fe33f1e691a1c8c037d8742d36e75e9573fc135ddd4da3411c83bf9315e479e8ef8cd7e39c7de3370f70655a996e161fdb99e537cb570386aea961b072fe5c7637d9578d3abff0050d4a956ad16bf7ca8f34247fa4bf449fd99de31fd2630384e33cf712bc28f0b3171f6b80e28cf32caf8dceb89a95f4abc2dc34eb602a62f012dbfb6b30c66032da89f3e5f5334953ab4a1eafa77fc13efe1cc23fe26be36f19df377fb08d174c527d76cda5ea8473ce371e9d7a1ab37dff04fef85f2c4469de2ff001d59cdda4bab8d0afa3cff00b5147a158311ec25527fbdd36fe2b3fa49714bc473c323c82385e65fb897f68cebb8f55f5a589a74f99ff37d52cb7e59688ff4f70ffb12fc01864cf0d8af157c5caf9fba724b36a1538470d95aacedcb3fec49f0ce2b12e945ef49e77cf25a7b64d291e4be30ff00827debd656725d781bc7565ae5d4619c695afe9afa2bcc072238352b6b8d46dda66190a2e2d6d212d8df3c632cbf0a78c3c11e2cf006b13681e31d0aff0040d561f9becf7b1e1278b3817167731992d6fad588212eace6b8b7620812965217f73f0dfc59cab8fbdae06a61ff00b273dc3c1d6965f3afede962b0f1694abe0abba545d4f6778fb7a13a71ab493534eb439aa43fca9fa6bfecf6e3efa237d438a7079cbf10fc29cdb134f2fa3c6187cb2795e3b21cdeb29ca8e53c5195c2be369613eb9184ff00b3335c3e2eae071f529ce855860718f0f86adcad15fad1fe79851400514005140051401fd43ffc1a2ffb356abf1bff00e0ad5a0fc53b4f19788bc0f63fb27fc1bf887f19a7bbd0741d17578fc5fa8789bfb2be07c5f0d355bef1058ea5a7f87f4df157877e2af8ab52d4353b4d3ee3c452697e18d46c3c3373e1fd62eedf1dd67c73e2dd053c41ac68de2a7d2f4db6d466925d1ede2b7d334bbe49df4b005b5e5c19af24cfdbd6ea00ab16c823914cadf97f8c39f667c3bc099963f28c43c2632a56c2609626293ab4a8e2eb2a55a5464e32f6759d36e30aaa3cf49bf694daa9184a3fddff00b377c26e05f197e95dc15c27e22e4d0e21e1ac1657c45c513c96bd59c30198e61c3b97fd7b2ca19a53a7caf19974716a9d6c560272587c6c69470d8c8e2307531186adfa7f0c30dbc51c16f1470410a2c70c30a2c51451a001238e340111140015554281800702a4aff3e25294e4e526e52937294a4dca5293776db7ab6deadbd5bd5f53fec36952a587a54a850a74e8d0a34e14a8d1a508d3a54a9538a853a74e9c528c29c20a3184231518c52495ac1452340af37f8a3f0abc21f173c3371e1bf165824ca564934bd561545d5344be65c25f69b72559a37042f9f0306b6bc897c9b98e44236fa9926718dc8336cbf39cbaa3a58ccbb134f1346576a32707efd2a96bf351af4dce8d683569d2a9384ae99f05e28f86fc2fe2ff00879c5fe19f1960a38ee1be33c8f1b9266549c60ead0589a7fecd9860e538c951cc72bc6470f9965b895173c2e3f0986c443dfa5071fc2af8a5f0d75ef84fe34d57c19e20557b8b1659ac6fe2575b5d5f4ab82c6c753b5de3222b8456592225dadaea2b8b591da5818af9e57fa6392e6b86cf328cb738c1b7f56ccf0586c7514dde508622946afb39d92fde537274ea2e5569c5ab1ff0e9e26700e75e167887c6de1bf11462b3be06e28cef85f319c2328d1c457c9b30af82fae61d4b5784c7428c319849bbfb4c356a55136a498515e99f0e1450014500145007faefff00c1b0dfb1468dfb21ff00c126be0378ab50f0959689f177f6b1d34fed2df12f5c12e81a96a7ae7877c7b35cde7c09b41ace8f6ff6c4f0fd97c1197c15af59785b56d4b51b8f0c78abc5be387922d2751d6354d2ed7fa19a17f5f3d5fe3e4bf22a6ef27aded68a7aeaa29453d527aa4b75eb7d18514121450014500145001450014500145001450014500145007f89b7fc1653e036b7fb35ff00c154bf6f9f853ac782747f875676bfb507c57f1c781fc1fe1d1e1e8bc3da47c26f8b5e27bdf8aff06c68563e169a6d0f46d22efe15f8d3c1f7b61e1cb55b39fc316f709e1dd4f4bd2354d2ef34bb2fcd0a072f89bbdeedbbddbbdf5bddeaef7bea93efd428a04145001450015fae3ff04feff9259e2dff00b1f6ebff004c1a0d7e2fe3e7fc9b9c77fd8cb2affd493fd37fd919ff00299fc31ff64471f7fea94fbbebf337e3b7eda1e37f0cf8c3c4fe04f03689a468c7c39ab5e68d71e20d4f7eaf7f753d9c8d149716564c9069d6519901d897716a8ccaaacdb0b145fe66f08b8132fe3be21c4e1735c456a597e598358ec450c3fb9571b7af4e8c30fedf7a14dca7cd56a4632a8e31e4a6e9ca6ab52ff71ff68b7d2bf8bfe8a1e0ee4d9ff00e519663b8bf8e38927c2794e6b9c296232fe1ae5cabca1a469479608fd1bfd9c7f6b7b3f8a77d0782bc6f6965a1f8da58dce977964d245a37890c28649608619de5934fd5444ad28b469a682f16391ada48a409695f6c57f0ff00891c173e05e28c564f19ceb602ad3863b2ac454d6a55c05794e318556a318bad87ad4eae1eac924aa3a4ab72c2352108ff00d4cfd0a3e93585fa56781391f88b5b0d84cb78b72ec66278578fb28c14a5f54c0f15e55470b5abe23010a939d6a796e7397e372fceb034aacaacb090c7cf2e962315570357113fcfcfdbff00c1f6d79e0af08f8e22b653a8689af36837574a0890e95acda5cdcc69363868a0d434f8843bff00d5497926cff5cd5f9455fd71e0463658cf0e72ca739b9bc063733c12bbbb8c562e78a84365a4618a8a8a77b47955da48ff009dbfdac5c2f43873e9a3c738cc361e387a7c5dc35c0fc5128c1350ab5ea70f61b21c562231b24a55f1590d5a959c6ea75dd4a926e7390515fb19fe6d0514005140057d2bfb1d7ecb7f12ff006d6fda83e07fecaff08b4c9f51f1cfc6cf885a0782ec6e12c756beb0f0d6937b76b2f8a7c75e235d0f4dd6353b3f07fc3ff0bc1ac78d7c65ab5b6977a747f0be83ab6a8f6d325a3230f6febf4bbfc3ef2a0b9a514f66f5f25d5eb65a2d5dda5a5dbfb51ff72cf867f0e3c15f073e1bfc3ef845f0d74287c2ff000e7e15f823c29f0e3c01e19b7bad42fadfc3be0af03e8361e19f0ae8505eead777faade43a4685a5d8584575a9df5eea17096e26bcbbb9b879677ede813776dbeadbf2d7d125ff0092af40a2810514005140051400514005140051400514005140051401fc0bff00c1ec9fb15433e93fb2b7fc144b46d54fdb2c2ea0fd8dbe22683797f23799a7de2fc49f8d1f08f5df0e6970680638a3b2bd8be32699e39d4755f1406964d57e1bdae85a0b795e21d453fcfbe8effd7f5fd6fb952b3b3b5bdd57d34bad2eb577ba49b7fcd7d344145048514005140057e8d7ec17f13f42d166f13fc37d6efedf4ebbd76fed75cf0db5dceb043a85f2db0b1d474d89e4023fb6bc3058cf6906fdf74b1dc88d59e20adf9878c994e2339f0ef3fa184a72ad88c2c30b98c2941394a74f018ba35f13cb18a6e4e3848d79a4b56e092bb763fbb7f66b788793786ff4c9f09335e21c6d0cbb26cf7119f7066271d89ab1a187a18be2be1fcc729c93db559da9d3a75f886b653869d4a928d3a71acea4e4a306e3fa3be31f1a7867c03a05ff00897c57ab5ae93a5584324cf2dc4a892dcba2164b4b180b092f2fae1808edad2057966919540e7727f3d9e32f114be2ef16f89fc533a7952f88f5fd5f5b78739f24ea77f3de0873ce4422658c7b2f7e0d7e47f46ac9f130ff005973eab4a70c2d6860b2cc255926a35ea42757118ce46d5a4a8ffb22934dae69f2fc507cbfe87fedbbf12324c43f04bc27c0e3f0f89cf72eafc4dc71c4381a55a13ad95e0f1787cbb26e1c962a9c64dd29e66d67f52953a8a33f63828d5e574eb5291cdd15fd567f8066ef85f52d4746f12f87f56d21a55d534dd6b4cbed3fc866494de5b5ec335baa32f3b9e5545c73bb3820e48afe909492aa48c120123d091c8fc0fb0afe4bfa4cd2a4b15c1f5d72fb6a987cee954fe674a8d4cb274afa7c2a75ab72eaf572d165001450014500145001450014500145001450014500145007ca9fb717ec97e02fdbaff00648f8fbfb247c4a916d3c2df1c7e1eeabe135d6cdadeea0de11f14c325b6b9e01f1edbe9761ad78766d62f3e1f78f749f0cf8decb45975ad3ec35abcf0fc1a5ea93369b77751bff88ffed2df01bc61fb2d7ed0ff001cbf66bf88177a3ea3e36f809f167e207c21f14ea9e1c97509fc39abeb5f0f7c51a9f85efb5af0e5c6ada768faadc787b599b4c6d5343b8d4b49d2efee34abbb49af34fb3b877b78975df75b6bd1ef6dbaaf3d16fa38def4b6f866ef2b2d7da4559376be9ecdb49b6b56d25ab3c4a8a640514005140052824104120839041c1047208239073ce78f5f7a37dc69b4d34da69dd34ecd35d535aa77ebf32cdcdede5e95379777576501086e6e269ca03c90be6bb9504f271d7be782b56a29d3a74a0a9d28429d38df9614e2a105777768c74576eef4577a9be2f198bc7e22a62f1d89c46331559a757138baf571388aae31518ba95ab4a5526d46318ae66ed18a4b44b94a500920019278007524f618e79ab39d26da495dbd125bb6fa2b6ba9fa15fb2a7ecafae5feb9a37c4cf88ba6cba4e85a4cf06abe1bf0f5fc4d1ea1addfc2cb3586a37f672c7bed348b59425d4115c2a5c6a33470b18bfb3cefbafd55afe08f1b38c30fc57c5f3a597d555b2cc8a83cb30d5a12e6a589c42ab2a98ec4d27f0ca9ceb72e1e9ce2dc6ad3c2d3aaaeaa4647fd707ecbefa38e71e007d1cb0f8de2fc054cb78ebc56cd63c739e65b89a6e963725c9e782a183e15c8f1d4e518d4a58ba196c2ae718bc3568c6be031d9e62b2fc4463570923e36fdb9bc491e8df04e4d1837effc59e24d1b4b541d4dbe9f249af5c4879fb8b26996b19e0fcd3271c864fc68afe84fa3d615e1fc3f9566bfdfb3dccb14bcd42960f057dbf9b08fbed76f650ff1d7f6c5e7f0ce3e97b4f2e84aef857c2ae0bc8aa47f96a6271bc45c4d6f9d2e21a32d7f1f75c4a2bf733fca90a2800a2803ea4fd87fe01db7ed51fb657eca9fb365fdfdce93a57c77fda17e107c28d6f57b3b09354b9d1b40f1cf8f342f0f78835a8b4e8ae6c5ef0e8fa35fdf6a6d07dbac11d2d5bccbeb48f75d41fee75e1bf0df877c1be1dd03c1fe0fd0345f0a784bc29a2e95e1bf0bf85fc37a558e87e1df0df87742b1834bd1340d0344d320b5d3347d1747d32d6d74ed2b4ad3ad6dac74fb1b682d2d208a0863445d5fa2fcdfe7d7d116ff00871d379ceeeef5b46164d6deeddb4f57ef3db436a8a6405140051400514005140051400514005140051400514005140057f98b7fc1e7dfb300f85fff00050bf82ffb4ce87e0e8744f0c7ed4ff00acac3c45e2a4d75afe5f1b7c69f80facbf843c5b34fa1dd6b17b79e1ffec0f83de21fd9eb4789acf49d17c37ac2426e74ff00ed1f115bf8bae90edfd7fc3ffc33e88a8bd26b4d636d55efef45e9a3b3d2f7d344d36ef63f8f0a282428a0028a0028a00294024803924e00f527f3eaa2f175aaf23777ecd46fd397fe22be949e31cfc7dfa4078a5e2c2f6cb2fe2ae27c43e1fa588e655a870b651468647c2d42b53925ecabc387f2dcbbeb54e315158a75a5ab9390515f547e0414500145007eef7fc1b35e08f0c78fff00e0b7dfb0c68de2dd306ada6697e20f8c3e37b2b46babcb458fc4ff000ebf678f8b7e3cf066a665b19ede790e89e2ef0ee89ad476af2fd8ef25d3e3b4d4a0bcd366bab39bfd86a81bd92e9abf9b767d3af2c7abdbff000128a041450014500145001450014500145001450014500145001450015fc1b7fc1f1cda5ffc2bcff8271acb677efad1f19fed3ad61a8476923e976ba5ae87f0386af67797c018adefefeedb439f4db476125e5be9daa4d1065b094a27faafcffaf52e1f13d2fee54e97de9cb5b2ececdbe9f133fcf428a64051400514005140054917fad8bfeba27fe84293d9fa334a5fc5a5ff005f21ff00a523fa588bfd545ff5cd3ff4114b2489123cb2ba471468d249248c112344059dddd885545505999880a0124f535fe5559b95926db95925ab6dbd124aedb6fcbefb9ff7ed19c29e1e352a4e30a74e8a9ce7392842108c39a539ca5eec6318a72949d9452bbee7cb9f11ff006bff0083de0117b6567abbf8d35fb50c8ba57867fd26d3ed0095f2ee75e64feca8515c62736d3dfdc4383fe8b230095f97df193f688f881f19ee4c3adddae93e198661358784f4a79134c89d0feea7be91b6cfab5ea8e45c5e13142fb9acad6d03b2d7f5bf833e116272cc450e2ee29c37b1c5d38c6ae499556fe2e1a538dd6638da767ecf1118bb6130d52d530f3e6af5a30af1a4a1ff003bdfb4c3f68a647c7393e6bf477f0133dfed2e1ec5d6ad82f1438f72e94e382cee8e1710e9cf83786717cb1faee4f5ebd1f699f67587ff0063cde846965997d7c565788cc2788f06a2bfa7cff09828a0028a0028a00fd96ff837b7e36e95f003fe0b39fb0078e359d264d66cfc43f1a24f8250dac370d6b2dbeabfb49f82fc57fb3c683ab0956cef898f44d77e2869bac5d5b98116f6cec6e2c9ef34e5b93a8d9ffb3151fd7f5fd7f98ddacb5d7556d745a34efb6adbdb6e5d778b0a28105140051400514005140051400514005140051400514005140057f119ff0007bafc31f176affb277ec51f18ec750f0fc7e04f007ed09e3af86fe26d26e5750ff84aaf3c55f167e1cffc24be0bd4f4668e16d28787b4bd2be0df8ead3c4cb7b7116a0da96b3e116d321b8b64d5a4b51ebfd79a7fd77fc6351766ff00c335b5f7835f8df7e9beb63fcdea8a090a2800a2800a2800a50482083820e41e9823be79c73edf9d034da69a7669dd3dacd75beb6d7cbef3f7c7e077c61f0bfc54f03683a8d96af61ff090c1a65a5af88f4492ee24d4f4fd5ada08e1bc67b472b3bd9dccc8d736576b1b433c12a7ceb3a4f141e19fb677c65f0d689f0e354f879a4eb1657fe2bf163da595dd8d8dcc5752e91a2c37515e5f5cea1e4c8c2d5ef05b2585bdbcc566992e659d6331c0cd5fc0dc2fc0f9ad3f1670590d4c16214726e228e3b155254aaf067c55e27d4fc6fa65af88b40f11689e22f883f067528a3d4f44d4357b5aff004c9a3bff005fa2ebebeaf41bd2cb4bd95edadefef26f57ad9c7d2d66ae985140828a0028a0028a0028a0028a0028a0028a0028a0028a0028a002b23c41e1fd07c59a0eb7e16f15689a47897c31e25d2352f0ff0088fc39e20d36cf59d07c41a0eb367369dac689ade91a8c373a7eaba46aba7dcdc58ea5a6df5bcf677d677135b5d432c32ba3009b4d34ecd6a9ad1a7dd35aa3fc63bfe0b71ff04d2bbff82567edf7f10ff671d2b50d6fc41f07fc43a2685f17ff0067af16f8924f0e9d7fc45f087c6b26a3696f6dae5bf87750ba8a0d63c0de38d03c6ff0cafef751d3bc2d7de2b93c12be3cb5f08787bc3fe2cd0ac97f23e8febfaff86454adccda4926ee92d927ad95f5b2dbe5a851412145001450015ada1685ac789b57d3f40d034ebad5b59d56e52d34fd3ece3325c5ccf2721517a2aaa869259642b1430a3cd3c89146ce98e2711430987af8bc4d5851c36168d5c4622b547cb0a5428c1d4ab5672b3e5853a7194e4f95d926eccf4b26c9f34e22ce32ae1fc8f0388cd33acf332c0e4f9465b8487b4c56619a6678aa582cbf0386a6b5a988c5e2ebd1a1461a73d4a915d6e7db3a0fec07f132feca3b9d73c51e14f0fdcc8a1bfb395b50d5ee20ce3e4b99adad62b3122f39fb2dc5dc648e243d579ff1c7ec39f163c2ba65ceada2de687e3482d2279a7b0d1e5bcb5d68c51a9791edec2fad6286ecaa824416d792ddc846c82da7728b5f83e13e90fc2189ce29e5f3c166985c055c42c3c338aca82a315297243115f0aa4ebd1c337694a57a9569d3b4e5493e68c3fd65e22fd8d9f48bc8fc38c5f17e1b8af80b3ce2cc064f3cdf15e1d6595b3796655a54683c462328caf3aab808e59986730847d951a0d61b038bc527468661283a15eafc64cac8cc8eac8e8c559181565653865653cab0390410083d79a6d7efc9a6934ee9ea9ad534faa7d6ff00d6e7f91328ca129425171945b8ca324d4a324ece324f54d3d1a6934d6ba85140828a0028a0028a00ff005d7ff8356fe19f867e1f7fc1123f658d6b44f0f2687af7c59f11fc7bf899e3fbbdd7be7f89fc4cff001d7e20780747f10dd45772bc5039f86fe02f00e896cba7c56d653e9da258de7952dddcdd5e5d7f44342b74d9ebf7ebfd7fc3152bf334ef78fbbae8d72fbb66bbab2be8bcc28a090a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2803f133fe0b91ff00046ef017fc160ff66ad23c1d1f89a3f871fb497c117f15f89ff669f897a9cfa9cbe0fb0d7bc5367a347e2af87df12748d3e1bf9a7f879f1257c2de18b5d63c45a369779e31f02eafa0f87fc5da0daf88f4dd33c45f0efc77fe40bf183e0cfc5afd9f7e23789be10fc72f86de37f847f147c1b71676be29f007c45f0ceb3e10f16e8726a5a658eb9a53ea1a16bb6761a8416dac685a9e97af68d76d6c2d757d0f53d3758d3a5bad36fed6ea53f5d7f2f979ff009ef1bb5e17d3dcf75ea936a5cd24f977959a7cd2d547dc52247146acf248ec7015110166663d02a924f63c57f47ff00f047eff83703f6c1ff0082857c5bf07f88ff00681f86df157f65afd8d2cacf41f1b78cbe2cf8ebc23a9782bc5ff143c1fab416bab687e1afd9ef44f18e950cbe27d4bc7da5cf1cb63f13e4d1f53f86de0bf0fcb71e2abc97c59ac47e18f87de344df4eaffabecff15ae8b7712e31ba736bdc8eeef64de9ee2ef2775a28ca4a3efb5cb1728ffabf7c32f86fe09f837f0dbe1f7c21f867a05bf853e1c7c2af04784fe1bfc3ff000bda5c5f5dda786fc13e06d06c3c31e14d02d6eb53babed4ae6df47d0b4bb0d3a1b8d42f6eefa68ed964bbbab8b86799fb8a643776df7d74d17c92492ffc057a051400514005140051400514005140051400514005140051400514005140057927c67f801f023f68ef0ad9f817f686f827f093e3cf8234ed76d7c51a7f83be33fc37f077c50f0ad8f89ac6c353d2ecbc4567e1ef1be8daee916baed9e99ad6b1a75aeaf0d9c77f6f61ab6a76915c25bdf5ca4a05edb7f97e5fd33fcf3ffe0e66ff0083787c31fb2ee9be35ff0082907ec3da0e85e16fd9da5d5f4abafda3bf679d33ec1a1e9bf04b5ef17f8874df0de99f113e0be963ecb6aff093c51e2ad6f4ad2fc43f0b34a8cde7c28f10eaf6da9f81ac2ebe0fdfdee87f04ff00892a0a974977dfb732b73744bac65a5edcd6bbb20a282428a002b5743d7357f0d6afa7ebda0ea173a56b1a55cc779a7ea167218ae2dae23fbae8c010cac0b24b1ba3c5344ef14d1cb13b256388c3d0c5e1ebe13134a15f0d89a3570f88a3515e9d5a15a12a75694d6b7854a729464adaa6f7b9e964d9c667c3d9be559fe498ec46599ce479960738ca332c24fd962b2fccf2cc552c6e031d86a8aee9e23098ba347114676f72a538cb5b1f6c683fb7dfc4fd3ed23b6d73c35e12f104f18da6fc47a8e91733e07dfb88ed6e65b2f30b60936f696d1f6110e0d73be35fdb87e2ef8a34db8d2b478341f06417513c335f68705ecdac849015905bea17f73711da3321c2cf696705dc2dfbc86e62902327e1384fa3c70761b378661531b9ae2b014abac453ca2bd4a0e84b965cf1c3d7c44682af5f0c9f2a70fddd49c172d4ad2bca52ff00583887f6c8fd24b3cf0ef13c2385e1ae00c8b8ab1d9554cab17e22e5785ce219b52f6b43eaf5736cab28ab9854ca72dcea5172ad0c43a78ac0e1b132f6d85cba8fb3a30a5f1b3bbc8ed248ccf23b33bbbb1677763b99999b25999892cc79279392734dafded2492492492b24b4492d925d11fe47ca529ca539ca539ce4e539c9b94a5293bca5294aee5293776deadbbbbb770a299214500145007f593ff0669fc34f0678e7fe0acde33f1678a7418357d6be0cfec75f17be21fc36d426babfb793c2de38d63e207c1af84979aedac367736f6f7d35cfc38f89df107c32f6daac57d611dbf88a7bc8ed5754b5d36f6cbfd4be8febfafebcfb0df4db6ede7d7bbbf577d2dda3ca5140828a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a00f9dff6bcf835aa7ed1bfb277ed3ffb3d6897da4e99ad7c77fd9dfe35fc1ad2352d7cdc8d0b4fd53e287c36f137822c2fb5a36765a95d8d26d2ef5c86e35136ba75fdcfd8e398c365752ec81ffc24a48de291e2914abc6ed1ba9eaae8c5594fb86041a3afddfd7e5f77a15a722eea4eefa59a8f2fe2a5f86fbc594504851400514005140051400514005140051401fdc97fc1905f046cf5cfda2ff6ebfda424d767b7bff85ff057e167c11b3f0cad94725aeaf67f1e3c75ad78f353d767d44dcacb693f8767fd9cb49b0b4b24b3b88f518fc51793cb7368da5431deff00a3351fd7e5ff000fd7ff00916fa2ecbefbb6fb2efe7b6ef40a28105140051400514005140051400514005140051400514005140051400514005140057f887ffc1587f67eb4fd96ff00e0a57fb707c0ad23c1fa9780bc29e07fda4fe291f87be14d523d6d66d2be16789bc4d7be2ff8542d6e3c4735d6b7a9e9179f0eb5ff000c5fe85ae6a1797f36bda35cd86b5f6fbe4be4bb95755e8f5fbb4f9eff0022d7f0e7afdba6f97be93f7bd22ec9f4bc95eef95c7f3de8a64051400514005140051400514005140051401fe9f1ff0006607c048be1ff00fc1367e317c73d57c0d7ba078a3f684fda7fc4d1693e34be8753b71e3ff847f093c1be10f0af849b495b997fb32f743f0b7c53d4fe3b6911ea7a65aacb26befe22d2f50bbb97d1adedf4efec0285fe7f9ebf8ff5b0e5bef7d23b7f8569a76d9f9ad6ef50a28105140051400514005140051400514005140051400514005140051400514005140057f9ddff00c1ea9fb0addf877e267ece1ff0514f07e89a55bf863e2268f17ecc7f1aaef4cd33c3ba3dcafc4bf0bc3e24f1bfc21f136bf7106a51f88bc6bae78dfe1f278dfc2326b33e83716fe12f0efc13f08683a97887cad5fc27a45a9fd7f5f7dfcff18d4a25bb6f44b57a1fc347fc14bbfe0f31bdb5d7f57f865ff0004b6f86da3ea3e1eb7d37c5be1fd5bf697fda13c23aafdab50d6a6bcd5345d0fc5bf043e14c1e21d39acf48d3f4e834ff19f8735ef8dd6335f6af7faa43a1f8c7e0668b6de1fbc87c4ff00c4bfed41fb5ffed3bfb697c4ad43e2efed51f1c3e217c70f1f5fc97de46abe38d7ee2fec3c3da7dfeb1aa6befe1df04f86605b4f0b7803c1d69ab6b7ab5de91e0af0468ba0784b42fb7dcc3a368d616f218a95afabf92d74f3d95defafcbccae6514e30eaad395fe3d765eec6d0f87ddbbe692e697d88c3e6fa2990145001450014500145001450014a091d3f11d8fd4743dbebfaa834edfd7f97f5f9c5f946e1be43cf2bb8afb6576f193dc13eb838f97fd6c3fe0d3df08fc2ef0d7fc114fe00eadf0fedf4187c57e3cf88ff1fbc5bf1be4d1b5cfed8bebbf8a36bf173c4de09d2ae7c516a2fef17c3daf7fc298f077c21b787445b6d2bfe29bb7f0f6b1269ef3eaf2ea7a825a68ddfb3befe4d5b57f3f3d5ae6347cb28ca70567a2a9049b51575ef45f2be58ca5cba735e2fdd4e49dcfe9128a66414500145001450014500145001450014500145001450014500145001450015f9b7fb4f7fc160ffe0985fb1cb6af67fb427edb9f017c25e23f0f78a1bc19e22f87de1bf16ffc2d6f8b1e1cf11c51ea725cd8f88fe107c21b5f1e7c50d062b07d22f6d354d4757f0859697a56a62d34ad4afadb53d474db3bb3fafeac9fe5f7ef1a8c5caf65b6edbe58addeb27eeabd9daf7bf67a23f9dbfda93fe0f55fd8cfe1b7885b40fd94bf661f8c3fb50db69dad78934cd5bc67e38f15e95fb37f82755d3f4cbbb4b6f0ef88fc026efc29f167c7dade95e278bfb4b506b5f1d7c3df85faee89670e92b79a3cd7fa96a165a07f0fff00f0539ff82b17ed71ff000560f8c1a5fc52fda5bc45a469ba078434a8f44f863f053e1dc7aee8bf077e18d8c90dbaeb57fe1af0d6b5ae7882feefc59e30bd81353f1978d7c43abeb3e25d6a48f4dd0e2bed3fc17e17f06f857c36bf2fbfb6bb2d9fdfa3f21be58dd464e52d9c95d46d7926a3a73494972eaf974e68ca32ba947f33e8a6405140051400514005140051400514005140057df9ff0004f9ff008299fed7ff00f04caf8b969f15ff00659f89da8f87edaeb53d26f7e207c28d76ef57d53e0d7c60b0d212faded746f8a1e02b4d4f4db3d7e2b7b2d5f58b4d235db4b8d2bc61e15fed5bfbdf07f89740d4a737aa357feadf95ff002d7adca84b91dec9a69a945ed28bdd3d1faa7cb78c94671f7a3191fdaf7ecd1ff07b87c0cd7ee34cd1ff006bbfd8bfe24fc33487c29669aa78f3e0178fb40f8b767ac78ee16d1adefa587e1bf8e74df8517de10f066a4afafead063e237c41d7b40fb368fa03c5e2837b79e24d3bfa62fd96bfe0b41ff04b4fdb3f5987c33fb3c7edaff06bc51e2fbed6346f0fe8de06f17dfebbf06fc7fe27d6fc437535968fa5f837c05f1ab42f877e30f1ad001450014500145001450014500145007e2efedc7ff0007027fc12c3f604d63c53e06f8b3fb46d878fbe32f84488356f81df0134abaf8b5f106cf5683c532f8535bf0cebba8e8d25b7c31f01f8cbc2f7567aa6a5e23f067c4cf889e09f14e9fa5698f2a68f757ba9f87ecb59fe52ff6c5ff0083d7be33f8924d5fc37fb0afecb1e0cf85fa334be36d26d7e287ed13ab5f7c46f1bdee9578f1d9781bc59a27c3af065e7857c1de02f1669366b73adea9a2788bc4df1afc3126b17761a5b7f69e95a2de4fe2757bedb7777fc172ebeaf4d9ae6fb3af2c20af51de57d2926eff000a69ce4a368abb5ee24e6f967197b37c933f984fda9ffe0b07ff000533fdb3bfe12eb1fda0bf6cef8e1e27f0878e574c8bc4df0bbc3be2ebbf873f07353b5d18e9726956771f08be1d2f853e1dcd6f6775a2e97aa0171e1b9a5bad76d0788afa7bad7a6b8d465fcd9249249249249249c924f5249e4927a9efd4d0925f3ddddbbfdff959244ca6e5a68a2b5518dd416895d46cb56a315293bce5ca9ce52769094532028a0028a0028a0028a0028a0028a0028a0028a0028a00294120820e08e411c107d411c8a00fd1efd95ffe0af3ff00052efd8b4784ac3f676fdb3be39783fc1fe068b54b7f0bfc2cd6fc6177f113e0be9369ad49aacfab5a5a7c19f88c9e2bf8630457d7bae6abaabbc3e158ee20d76eff00e121b29edb5e82d752b7fe9f3f63aff83d7be377861f46f0cfedd3fb2c7827e2be8c93f82749bdf8a7fb3d6b17df0cfc7763a2d962c7c75e2dd73e1c78c2e3c5be0af885e33d5ed8c7af699a27873c4ff02fc269ac5b5de908ba5695abd9dd78615adf0ffe02dbb6d656f75f2ecb6baeaf57735e78cff8b7bbff009791d67acb99b9ae54aab6dbbca72552ed7bee31503fab3fd86bfe0e0dff0082577edf9acf853c09f0abf689b5f875f19fc61be1d23e067ed03a3dcfc25f1fddeab3f8ba1f07e83e17d0f58d524bcf853e3df1af8b6eef749d4fc31e06f865f12bc6fe2fd4747d5629a4d16defb4bf10e9fa2fed5509dfcbba7bafc12fbaebcd9138383dd493bf2ce2ef1924ed7578c5abefcb28a924d3718dd0514c90a2800a2800a2800a2800a2800a2803f2d7e377fc16e3fe0923fb3df8697c55f117fe0a11fb305ed8b6ab65a37f657c2af897a5fc7ef1a4777a859dddf5b5c4ff000f3e030f897e3eb7d1fecf6521b8f105c7869341b39a6b0b5bcd4adeeb53d361bafcd9f8e1ff0007737fc119fe135a68771e04f88df1cbf69a9f5697518f51d3be07fc09f15e8777e165b2fecefb34daecdfb46cbf0034db98b57fb6dc1d3c785efbc49345fd91a87f6b45a59934a1aa2bf6d7d3cedafa75eb7f5f86dc1c5da5683f7aea4e574e37f76514a528c9b56b4a3bb4e568fbd1fe79ff006f6ff83cff00f688f89da46b5e03ff00827dfc0fd33f664b2975bd620b4f8f3f15eefc39f173e2d5379a9da7857c05e1cb1d46c3c3ba76b36de12f85de04b0f0b7c35f07c7e2387c25e1bb8f157fc231e13d25bc57aa68f67adf890ea9ac235f316bef67e5adbe7a2bebafddd931f3f23bd3724ecd39ddc64efa3b597ba9ad1d9b6d5efeecad0f8968a6661450014500145001450014500145001450014500145001450014500145001450015f6afec85ff0519fdb87f60bf18c9e38fd92bf696f895f07b55bab4b6b0d5b47d3b52b3f12f807c4b67a7e9fe23d3746b7f187c2df1c58789fe19f8ce3f0e41e2ef12dc785078abc25ac1f09eabac5debbe1b3a5eb4c97f1164fe5b7f5fd5fa94a728a94536a3256946ef964b7f7959a76766af1ba92525aa8c8fe993fe09fff00f07937ed7df08f5ab3f0bffc1413c07a2fed69f0ef52d6e17d4be26780b47f077c20f8e7e0fd32faff00408350b9d3bc3fe15d0bc3df07be2158681a2d9ebd7ba2f832ef41f86bac6b9e20d5e24d5be2de9ba3d9dbdb41fd177c15ff0083c03fe08f1f1535ebdd1fc71acfed27fb37585ae977d7f6fe29f8d5f045b5bd0354bcb4bdd1ad6df43b287f678f157c7af1245aa6a90ea77ba958cfaa78774dd0e2b2f0eeb09a9eb1a76a13787acb5a5aaf3d77db47dfdd57fbbd6df14aa5ece56714a9be5f7a2e5371949595e0fd9b6b996ae33768b8c9fb4b4a118fe977c0cff82eaffc120bf68ab3d56f7e1cff00c1417f674d3974796d61b9b3f8c1e2e9ff00675d62e5af16f5e36d1b41fda134ef85dadf88618974fb86beb8d034ed4edf4d5368da8c968350d3cdc7eb1d3bafebfcbcdeddfcf464b84a2936b47a2927cd16d28b69497bada528f325ac6fef6b6e528a090a2800a2803e00f8ebff000558ff00826afecd2be3187e35fedd1fb2ef8335ef006a8da1f8bfc08bf193c17e25f8a7a26b50f8817c2f7ba3cff08fc21a9f883e274faa693ae79d69af69f67e12b9bbf0fc7a7ead7dadc56161a36a97561f867fb5a7fc1e25ff0004c2f82da4dfda7ecd1a47c5ff00db37c6d2787f4fd57c3e7c35e13d6be087c296d62e75bbcb0bff000bf8bfc71f19342d1be23787eff4dd1ec0ebada8f86be0878fb44bc1aae81a75b6a0d7336bd278655d74d7e6edd7af2bea9dedaaebd0be46b59fb9e4efcff65e94f57aa9271728c212d7ded1b87f3f9fb5bffc1e89fb737c58d2aefc3bfb24fc04f841fb22586a7e1ed36d2e7c63e22d52e3f692f8b5a2789ed3c4126a57fadf83b59f13785fc07f092cf48d57438ac7c392f87fc63f03fc75716c926b9ab59ebd0dfdf68e7c37fcdffed75ff0536fdbf7f6f0bebf9ff6b1fdac3e317c5ed1750d5f42f109f8797fe267f0cfc1ad3bc41e1bd064f0ce8daff87be077822dfc33f07fc31af5b68d717d6f3eb5e1df03e99aa6a371aaeb5a96a77579aa6bbabdeddbfebfafebf462e64be1d37bbbbe677566b48d92dfcf5b36f471f8568a090a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800a2800afbabf644ff829b7edf9fb075f58cffb26fed5df187e1068b63aceb9e233f0f34ff12b7897e0dea7e22f126811f86358f10788be07f8dadbc4df07fc51af5ce8b6f636d0eb5e23f046a9a9e9f71a4685a9e9d776baa683a2de581fd7f5fd6bd469b57b75566ba35e6b5bebaad347692bbb1fd13fecb1ff00079e7fc141be15dbf85740fda83e0cfc09fdabfc3da25a6b717887c51616da9fc02f8d1e35bbbe9356bbd1ae751f14f836dfc45f06f465d0ee6f74bb130f87ff0067ad3d752f0f68b1d9dd6df105fddf8aabfa1efd927fe0f0ff00f8260fc6bd0eda1fda674cf8b7fb17f8e6db43bad4b5d87c4de13f107c71f85d36aa9adad8d9f877c17e39f835e1cd6bc7fafdedce8f3dbeb73df78b3e0a7c3cd26d1adf56d345eddcb69a65c6b2b5f5edd3b6fa3bebafd9e9eb1b7ece4b4f724936d49b7093bc9da0d4138fbbc91519f35da94a55358423fb8df027fe0abbff0004d1fda593c192b3e928caa41a6b67782d55fdef7651ff001e5a2a8c028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a0028a00ffd9, 'image/jpeg', 'Y');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Wecken`
--

CREATE TABLE IF NOT EXISTS `Wecken` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UID` int(11) NOT NULL DEFAULT '0',
  `Date` int(11) NOT NULL,
  `Ort` text NOT NULL,
  `Bemerkung` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `Wecken`
--

INSERT INTO `Wecken` (`ID`, `UID`, `Date`, `Ort`, `Bemerkung`) VALUES
(4, 1, 1307109840, 'Tent 23', 'knock knock leo, follow the white rabbit to the blue tent'),
(5, 1, 1307109840, 'Tent 23', 'knock knock leo, follow the white rabbit to the blue tent');
