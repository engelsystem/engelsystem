[![Codacy Badge](https://api.codacy.com/project/badge/Grade/20b3b0b4e93344a29da6bec77f329e7a)](https://www.codacy.com/app/engelsystem/engelsystem)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)]()

# Installation eines frischen Engelsystems

## Mindestvorrausetzungen (bzw. getestet unter):
 * PHP 5.4.x (cgi-fcgi)
 * MySQL-Server 5.5.x
 * Webserver mit PHP-Anbindung, z.B. lighttpd, nginx oder Apache

## Vorgehen:
 * Klonen des `master` inkl. submodules in lokales Verzeichnis: `git clone --recursive https://github.com/engelsystem/engelsystem.git`
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

Fehler bitte auf Github melden: https://github.com/engelsystem/engelsystem/issues
