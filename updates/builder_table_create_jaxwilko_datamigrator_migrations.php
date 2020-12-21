<?php

namespace JaxWilko\DataMigrator\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateJaxWilkoDataMigratorMigrations extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_datamigrator_migrations', function($table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id')->unsigned();
            $table->string('table', 255);
            $table->string('hash', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_datamigrator_migrations');
    }
}
