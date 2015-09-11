# Engelsystem

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

Fehler bitte auf Github melden: https://github.com/engelsystem/engelsystem/issues
