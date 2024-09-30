# Arbeiten mit [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.4/reference/introduction.html)

Migrations bilden die Veränderung der Datenbankstruktur in programmierter Form ab. Jede Strukturänderung wird in einer einzelnen (jeweils neuen) Migrationsdatei abgelegt, die Teil des Moduls ist.

## Anwenden der noch nicht ausgeführten Migrations

Doctrine überwacht selbst, welche Migrationen schon ausgeführt wurden und verhindert damit mehrfache Ausführungen der selben Migration.

Für dieses Modul können Migrationen auf verschiedenen Arten ausgeführt werden:

- durch Aktivieren des Moduls im Shopbackend oder
- durch Ausführen aller Shopmigrationen über den OXID Migration Wrapper mit `./vendor/bin/oe-eshop-db_migrate migrations:migrate` oder
- durch Ausführen dieser Modulmigration über den OXID Migration Wrapper mit `./vendor/bin/oe-eshop-db_migrate migrations:migrate d3totp` oder
- durch Ausführen dieser Modulmigration über die Doctrine Migrations mit `./vendor/bin/doctrine-migrations migrate --configuration ./vendor/d3/oxid-twofactor-onetimepassword/migration/migrations.yml --db-configuration ./vendor/d3/oxid-twofactor-onetimepassword/migration/migrations-db.php`

## Erstellen eines Skeletons für die erste oder zusätzliche Migrationen

Passe die `migrations.yml` an Dein Modul an.

```
./vendor/bin/oe-eshop-doctrine_migration migrations:generate d3moduleid
```

Arbeite die angelegte Datei entsprechend Deinen Anforderungen um.

## Abweichungen zwischen Doctrine Migrations und dem OXID Migration Wrapper

In den originalen Doctrine Migrations können keine Suiten angegeben werden. Dafür gibt es die Möglichkeit, die Richtung (up / down) und eine Zielversion anzugeben.

Bei OXID können Migrations ausschließlich aufwärts (up) und immer fix bis zur aktuellsten Version ausgeführt werden.