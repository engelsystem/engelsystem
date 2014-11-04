# Installation eines frischen Engelsystems

## Mindestvorrausetzungen (bzw. getestet unter):
 * PHP 5.4.x with Suhosin-Patch (cgi-fcgi)
 * mysqld  Ver 5.1.49-3 for debian-linux-gnu on x86_64 ((Debian))
 * Webserver mit PHP-Anbindung, z.B. lighttpd, nginx oder Apache

## Vorgehen:
 * Auschecken der Dateien vom master unter https://vcs.wybt.net/engelsystem/git/
 * Der Webserver muss Schreibrechte auf das Verzeichnis import bekommen, für alle anderen Dateien reichen Leserechte.
 * Der Webserver muss auf public als http-root zeigen.
 * Empfehlung: Dirlisting sollte deaktiviert sein.
 * Es muss eine MySQL-Datenbank angelegt werden und ein User existieren, der alle Rechte auf dieser Datenbank besitzt.
 * Es muss die db/install.sql importiert/ausgeführt werden.
 * Erstelle bei Bedarf eine config/config.php, die die Werte (z.B. DB-Zugang) aus der config/config.default.php überschreibt.
 * Engelsystem im Browser aufrufen, Anmeldung mit admin:asdfasdf vornehmen und Admin-Passwort ändern.

Das Engelsystem ist jetzt einsatzbereit.

Fehler bitte im auf Github melden:
https://github.com/engelsystem/engelsystem
