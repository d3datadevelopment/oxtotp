# Working with [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.4/reference/introduction.html)

Migrations map the changes to the database structure in programmed form. Each structural change is stored in a single (new) migration file, which is part of the module.

## Applying the migrations that have not yet been executed

Doctrine itself monitors which migrations have already been executed and thus prevents multiple executions of the same migration.

For this module, migrations can be executed in various ways:

- by activating the module in the shop backend or
- by executing all shop migrations via the OXID migration wrapper with `./vendor/bin/oe-eshop-db_migrate migrations:migrate` or
- by executing this module migration via the OXID Migration Wrapper with `./vendor/bin/oe-eshop-db_migrate migrations:migrate d3totp` or
- by executing this module migration via the Doctrine Migrations with `./vendor/bin/doctrine-migrations migrate --configuration ./vendor/d3/oxid-twofactor-onetimepassword/migration/migrations.yml --db-configuration ./vendor/d3/oxid-twofactor-onetimepassword/migration/migrations-db.php`

## Create a skeleton for the first or additional migrations

Adapt the `migrations.yml` to your module.

```
./vendor/bin/oe-eshop-doctrine_migration migrations:generate d3moduleid
```

Edit the created file according to your requirements.

## Differences between Doctrine Migrations and the OXID Migration Wrapper

No suites can be specified in the original Doctrine Migrations. However, it is possible to specify the direction (up / down) and a target version.

With OXID, migrations can only be executed upwards (up) and always fixed up to the latest version.