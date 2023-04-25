<?php

namespace CA\Projects\Tests\Classes;

use JaxWilko\DataMigrator\Classes\Utils;
use JaxWilko\DataMigrator\Models\Settings;
use System\Tests\Bootstrap\PluginTestCase;

class UtilsTest extends PluginTestCase
{
    public function testFilePrepend(): void
    {
        $file = temp_path('example.txt');

        file_put_contents($file, 'is a test');
        Utils::filePrepend($file, 'This ');

        $this->assertEquals('This is a test', file_get_contents($file));

        file_put_contents($file, 'Hello');
        Utils::filePrepend($file, 'World ' . PHP_EOL);

        $this->assertEquals('World ' . PHP_EOL . 'Hello', file_get_contents($file));
    }

    public function testGetTableFilePath(): void
    {
        Settings::set('storage', 'datamigrator');
        $test = Utils::getTableFilePath('example');

        $this->assertIsString($test);
        $this->assertEquals(storage_path('datamigrator') . '/example.csv', $test);

        Settings::set('storage', 'testing');
        $test = Utils::getTableFilePath('example');

        $this->assertIsString($test);
        $this->assertEquals(storage_path('testing') . '/example.csv', $test);
    }
}
