# Engelsystem

Aus einer Mail von Lukas:

> Moin *,
> 
> ich hab die letzten Tage durch den Aufbau von Freifunk u.a. rund um den Hbf einiges der Helfer-Koordination mitbekommen.
> Aktueller Stand: $Person erstellt ein Doodle, schickt den Link überall rum, Doodle is closed (niemand sieht nix von niemandem), ~20 Leute werden benötigt, 150 Stehen vor der Tür -> 130 sind frustriert ^^
> 
> Mir is da sofort das Engelsystem vom Camp eingefallen. Ich war mal so
> frei und hab eins aufgesetzt: https://www.engel-muc.de/

Dieses Github-Projekt diehnt zur Organisation der noch offenen TODOs, sowie der Veröffentlichung unserer Anpassungen des Quellcodes der Software.

Fehler bitte hier melden: https://github.com/muccc/engelsystem/issues

## FAQ

* Was ist ein "Engel"? 
  * Ist bei uns aus "historischen Gründen" der Betriff für freiwillige Helfer.

## Aufsetzen des Development Environments

 * Vagrant installieren
 * optionale Vagrant Plugins:
   * für LXC Support: `vagrant plugin install vagrant-lxc`
   * um per Hostname auf die VM zuzugreifen: `vagrant plugin install vagrant-hostmanager`
 * Submodules: `git submodules update -i`
 * VM starten: `vagrant up`

## Installation eines frischen Engelsystems

### Mindestvorrausetzungen (bzw. getestet unter):
 * PHP 5.4.x (cgi-fcgi)
 * MySQL-Server 5.5.x
 * Webserver mit PHP-Anbindung, z.B. lighttpd, nginx oder Apache

### Vorgehen:
 * Klonen des `master` inkl. submodules in lokales Verzeichnis: `git clone --recursive https://github.com/engelsystem/engelsystem.git`
 * Der Webserver muss Schreibrechte auf das Verzeichnis `import` bekommen, für alle anderen Dateien reichen Leserechte.
 * Der Webserver muss auf `public` als http-root zeigen.

 * Empfehlung: Dirlisting sollte deaktiviert sein.
 * Es muss eine MySQL-Datenbank angelegt werden und ein User existieren, der alle Rechte auf dieser Datenbank besitzt.
 * Es muss die db/install.sql importiert/ausgeführt werden.
 * Erstelle bei Bedarf eine config/config.php, die die Werte (z.B. DB-Zugang) aus der config/config.default.php überschreibt.
 * Engelsystem im Browser aufrufen, Anmeldung mit admin:asdfasdf vornehmen und Admin-Passwort ändern.

Das Engelsystem ist jetzt einsatzbereit.

