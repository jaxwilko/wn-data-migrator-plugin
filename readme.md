# Data Migrator

Manage your data via your VCS.

## About

This plugin allows for the transportation of data by converting you tables to flat data files. Once a table has been
flattened it can then be reimported.

## Install

```bash
composer require jaxwilko/datamigrator
```

## Usage

Via the plugin settings page you can configure which tables you want to be flattened/migrated and specify 
the directory to store the flat data files in.

This plugin is used via the `artisan` cli.

| Command        | Function                                                     |
|----------------|--------------------------------------------------------------|
| `data:flat`    | Write your selected tables to storage                        |
| `data:migrate` | Read the flat files in storage and import them if necessary  |

After you've flattened your data, you can use your VCS of choice to commit them. This can be very useful when developing
locally and deploying to a server as after pulling down your changes you can run `php artisan data:migrate` to update 
all flattened tables.

## Notes

The migration system tracks when files were migrated using a sha1 hash of the migration content. If for some reason you
need to re-migrate, you can delete the migration record from the `jaxwilko_datamigrator_migrations` table.



