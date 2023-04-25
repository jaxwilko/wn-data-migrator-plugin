<?php

namespace JaxWilko\DataMigrator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JaxWilko\DataMigrator\Classes\Utils;
use JaxWilko\DataMigrator\Models\Settings;
use JaxWilko\DataMigrator\Models\Migration;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Migrate extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'data:migrate';

    /**
     * @var string The console command description.
     */
    protected $description = 'Rebuild from flat files';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if ($this->option('table')) {
            $this->rebuildTable($this->option('table'), $this->option('force'));
            return;
        }

        $tables = Settings::get('tables');

        if (in_array('system_settings', $tables)) {
            // Always process the system table first if it's being tracked
            $this->rebuildTable('system_settings', $this->option('force'));

            // Reload the tables from the newly imported settings
            $tables = Settings::get('tables');
            unset($tables[array_search('system_settings', $tables)]);
        }

        foreach ($tables as $table) {
            $this->rebuildTable($table, $this->option('force'));
        }
    }

    protected function rebuildTable(string $table, bool $force = false)
    {
        if (!$force && Migration::imported($table)) {
            $this->warn(sprintf('%s does not need importing', $table));
            return true;
        }

        $filePath = Utils::getTableFilePath($table);

        try {
            $csv = Reader::createFromPath($filePath, 'r');
        } catch (\Throwable $e) {
            $this->error(sprintf('unable to read table source: `%s`', $table));
            return false;
        }

        $csv->setHeaderOffset(0);
        $hash = $csv->getHeader();

        if ($hash[0] !== 'hash') {
            throw new \ErrorException(sprintf('table `%s` has no hash header', $table));
        }

        $notNulls = [];

        foreach (DB::select(DB::raw(sprintf('EXPLAIN %s', $table))) as $column) {
            if ($column->Null === 'NO') {
                $notNulls[] = $column->Field;
            }
        }

        $model = DB::table($table);

        Schema::disableForeignKeyConstraints();
        $model->truncate();

        $csv->setHeaderOffset(1);
        $records = (new Statement())->offset(1)->process($csv);

        foreach ($records as $record) {
            foreach ($record as $key => $value) {
                if ($value === '' && !in_array($key, $notNulls)) {
                    $record[$key] = null;
                }
            }

            $model->insert($record);
        }

        Schema::enableForeignKeyConstraints();

        (new Migration([
            'table' => $table,
            'hash' => $hash[1]
        ]))->save();

        $this->info(sprintf('%s built', $table));

        return true;
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['table', 't', InputOption::VALUE_OPTIONAL, 'Table to migrate.', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force migration to re-run.', null]
        ];
    }
}
