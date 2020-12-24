<?php

namespace JaxWilko\DataMigrator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JaxWilko\DataMigrator\Classes\Utils;
use JaxWilko\DataMigrator\Models\Settings;
use JaxWilko\DataMigrator\Models\Migration;
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

        foreach (Settings::get('tables') as $table) {
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
            $file = new \SplFileObject($filePath, 'r');
        } catch (\Throwable $e) {
            $this->error(sprintf('unable to read table source: `%s`', $table));
            return false;
        }

        $file->setFlags(
            \SplFileObject::READ_CSV
            | \SplFileObject::SKIP_EMPTY
            | \SplFileObject::READ_AHEAD
        );

        $hash = $file->fgetcsv();

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
        Schema::enableForeignKeyConstraints();

        $headings = $file->fgetcsv();

        while(!$file->eof()) {
            $record = $file->fgetcsv();
            if (empty($record)) {
                continue;
            }

            $data = array_combine($headings, $record);

            foreach ($data as $key => $value) {
                if ($value === '' && !in_array($key, $notNulls)) {
                    $data[$key] = null;
                }
            }

            $model->insert($data);
        }

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
