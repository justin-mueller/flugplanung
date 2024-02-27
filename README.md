# flugplanung

Flugplanung für Gleitschirm-Vereine mit Windenschleppbetrieb

## Installation

### Technische Voraussetzungen

- PHP 8
- MySQL / MariaDB

### Konfiguration

- Source auf Server auspacken
- Datenbank aus `flugplanung.sql` importieren
- In `db_connect.php` die Datenbank-Verbindung konfigurieren

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
