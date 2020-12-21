<?php

namespace JaxWilko\DataMigrator\Models;

use JaxWilko\DataMigrator\Classes\Utils;
use Model;

class Migration extends Model
{
    public $table = 'jaxwilko_datamigrator_migrations';

    protected $fillable = [
        'table',
        'hash'
    ];

    public static function imported(string $table)
    {
        $fp = fopen(Utils::getTableFilePath($table), 'r');
        $hash = fgetcsv($fp);
        fclose($fp);

        if ($hash[0] !== 'hash') {
            throw new \ErrorException(sprintf('table `%s` does not include a hash header', $table));
        }

        return !!static::where([['table', '=', $table], ['hash', '=', $hash[1]]])->first();
    }
}
