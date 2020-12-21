<?php

namespace JaxWilko\DataMigrator;

use October\Rain\Support\ServiceProvider as OctoberServiceProvider;

class ServiceProvider extends OctoberServiceProvider
{
    public function boot()
    {
        $this->app->singleton('jaxwilko.datamigrator.flat', function () {
            return new Console\Flatten();
        });

        $this->commands('jaxwilko.datamigrator.flat');

        $this->app->singleton('jaxwilko.datamigrator.migrate', function () {
            return new Console\Migrate();
        });

        $this->commands('jaxwilko.datamigrator.migrate');
    }
}
