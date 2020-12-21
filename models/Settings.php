<?php

namespace JaxWilko\DataMigrator\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'jaxwilko_datamigrator_settings';

    public $settingsFields = 'fields.yaml';

    protected $cache = [];

    protected static $excludeTables = [
        'migrations',
        'jaxwilko_datamigrator_migrations'
    ];

    public static function availableTables(): array
    {
        $tables = [];

        foreach (DB::select('show tables') as $table) {
            try {
                $table = array_values((array) $table)[0];

                if (in_array($table, static::$excludeTables)) {
                    continue;
                }

                $tables[] = $table;
            } catch (\Throwable $e) {
                Log::error('error thrown decoding table', (array) $table);
                continue;
            }
        }

        asort($tables);

        return array_combine($tables, $tables);
    }

    public static function tableExists(string $table): bool
    {
        return isset(static::availableTables()[$table]);
    }
}
