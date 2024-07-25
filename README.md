# flugplanung

Flugplanung für Gleitschirm-Vereine mit Windenschleppbetrieb

## Installation

### Technische Voraussetzungen

- PHP 8 mit PDO
- MySQL / MariaDB
- Composer

### Installation

- Source auf Server auspacken
- Datenbank aus `flugplanung.sql` importieren
- `composer install` aufrufen

Nach einem Update ebenfalls `composer install`, um ggf. neue Dependencies zu installieren.

### Konfiguration

- In `config.php` die Default-Settings überschreiben.

Die Default-Settings stehen in`config.dist.php`:

- die `clubId` (`198` , d.h. HGDF)
- die Fluggebiete (`sites = ['Neustadt-Glewe', 'Hörpel', 'Altenmedingen']`, für den HGDF)
- In `db` die DB-Zugangsdaten (`servername`, `username`, `password`, `dbname`, `port`)
- Der `basePath` für die Asset-URLs (`flugplanung`, für den HGDF)
- die verfügbaren Vereine

Die DB-Zugangsdaten weren aus Environment-Variablen gelesen, wenn gesetzt:

- `DATABASE_HOST`
- `DATABASE_USER`
- `DATABASE_PASSWORD`
- `DATABASE_NAME`
- `DATABASE_PORT`

Wenn die nicht gesetzt sind, gilt

- `localhost` als Server,
- `flugplanung` als Datenbank,
- `3306` als Port.

Eine optionale Datei `config.php` wird ebenfalls gelesen und überschreibt die Default-Konfiguration aus `config.dist.php`. Die `config.php` wird in `.gitingore` ignoriert und kann die Konfiguration für eine spezifische Instanz anpassen.

Über `basePath` kann bei Betrieb in einem Verzeichnis unterhalb des Webserver-Root das Laden der Assets ermöglicht werden.

Nun kann die Flugplanung aufgerufen werden und die Registrierung von Nutzern ist möglich.

### Administratoren einrichten

Um einen Benutzer zum Administrator zu machen, muss diesem in der Datenbank in der Spalte `dienste_admin` von Hand der Wert `1` eingetragen werden.

## Benutzung

…

## Entwicklung

### Lokalen Server starten:

```
cd <project>
php -S localhost:8080
```
