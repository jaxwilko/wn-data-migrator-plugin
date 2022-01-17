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

    public static function imported(string $table): bool
    {
        $path = Utils::getTableFilePath($table);

        if (!file_exists($path)) {
            return true;
        }

        $fp = fopen($path, 'r');
        $hash = fgetcsv($fp);
        fclose($fp);

        if ($hash[0] !== 'hash') {
            throw new \ErrorException(sprintf('table `%s` does not include a hash header', $table));
        }

        $result = static::where('table', '=', $table)
            ->orderBy('id', 'desc')
            ->first();

        return ($result->hash ?? null) === $hash[1];
    }
}
