# Installation eines frischen Engelsystems
[![Code Climate](https://codeclimate.com/github/fossasia/engelsystem/badges/gpa.svg)](https://codeclimate.com/github/fossasia/engelsystem)
[![Build Status](https://travis-ci.org/fossasia/engelsystem.svg?branch=documentation)](https://travis-ci.org/fossasia/engelsystem)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d56c5bb224f24946965770230e7253c2)](https://www.codacy.com/app/dishant-khanna1807/engelsystem_2?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=fossasia/engelsystem&amp;utm_campaign=Badge_Grade)
[![CircleCI](https://circleci.com/gh/fossasia/engelsystem/tree/development.svg?style=svg)](https://circleci.com/gh/fossasia/engelsystem/tree/development)
[![Dependency Status](https://www.versioneye.com/user/projects/577c9495b50608003eee0161/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/577c9495b50608003eee0161)
[![Dependency Status](https://gemnasium.com/badges/github.com/fossasia/engelsystem.svg)](https://gemnasium.com/github.com/fossasia/engelsystem)
## Mindestvorrausetzungen (bzw. getestet unter):
 * PHP 5.4.x (cgi-fcgi)
 * MySQL-Server 5.5.x
 * Webserver mit PHP-Anbindung, z.B. lighttpd, nginx oder Apache

## Vorgehen:
 * Klonen des `master` inkl. submodules in lokales Verzeichnis: `git clone --recursive https://github.com/fossasia/engelsystem.git`
 * Der Webserver muss Schreibrechte auf das Verzeichnis `import` bekommen, für alle anderen Dateien reichen Leserechte.
 * Der Webserver muss auf `public` als http-root zeigen.

 * Empfehlung: Dirlisting sollte deaktiviert sein.
 * Es muss eine MySQL-Datenbank angelegt werden und ein User existieren, der alle Rechte auf dieser Datenbank besitzt.
 * Es muss die db/install.sql und die db/update.sql importiert/ausgeführt werden.
 * Erstelle bei Bedarf eine config/config.php, die die Werte (z.B. DB-Zugang) aus der config/config.default.php überschreibt.
 * Engelsystem im Browser aufrufen, Anmeldung mit admin:asdfasdf vornehmen und Admin-Passwort ändern.

Das Engelsystem ist jetzt einsatzbereit.

## Session Einstellungen:
 * Einstellungen für Cookies und Sessions bitte in der PHP Config des Servers vornehmen.
 * Sowohl Apache als auch nginx bieten Möglichkeiten für verschiedene Konfigurationen pro VirtualHost an

Fehler bitte auf Github melden: https://github.com/fossasia/engelsystem/issues
