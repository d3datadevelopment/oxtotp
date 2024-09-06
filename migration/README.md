# Arbeiten mit [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.6/reference/introduction.html)

Migrations bilden die Veränderung der Datenbankstruktur in programmierter Form ab. Jede Strukturänderung wird in einer einzelnen (jeweils neuen) Migrationsdatei abgelegt, die Teil des Moduls ist.

Passe die `migrations.yml` an Dein Modul an.

## Erstellen eines Skeletons für die erste oder zusätzliche Migrationen

```
./vendor/bin/oe-eshop-doctrine_migration migrations:generate d3moduleid
```

Arbeite die angelegte Datei entsprechend Deinen Anforderungen um.

## Ausführen der noch nicht ausgeführten Migrations

Doctrine überwacht selbst, welche Migrationen schon ausgeführt wurden und verhindert damit mehrfache Ausführungen der selben Migration.

Im OXID-Shop werden Migrations mit folgendem Befehl ausgeführt:

```
./vendor/bin/oe-eshop-db_migrate migrations:migrate
```

Als Argument kann noch die Suite mitgegeben werden, wenn nur bestimmte Migrations ausgeführt werden sollen. Mögliche Angaben sind:

- CE - für alle CE-Migrations
- PE - für alle PE-Migrations
- EE - für alle EE-Migrations
- PR - für alle Projekt-Migrations
- ModuleId - für alle Migrations des jeweiligen Moduls
- ohne Angabe - werden die Migrations aller Suiten nacheinander ausgeführt

## Abweichungen zwischen Doctrine Migrations und dem OXID Migration Wrapper

In den originalen Doctrine Migrations können keine Suiten angegeben werden. Dafür gibt es die Möglichkeit, die Richtung (up / down) und eine Zielversion anzugeben.

Bei OXID können Migrations ausschließlich aufwärts (up) und immer fix bis zur aktuellsten Version ausgeführt werden.