<?php

namespace JaxWilko\DataMigrator\Classes;

use JaxWilko\DataMigrator\Models\Settings;

class Utils
{
    public static function filePrepend(string $filePath, string $insert): void
    {
        $fp = fopen($filePath, "r+");
        $insertLength = strlen($insert);
        $combinedLength = filesize($filePath) + $insertLength;
        $overwrite = fread($fp, $insertLength);
        rewind($fp);
        $i = 1;

        do {
            fwrite($fp, $insert);
            $insert = $overwrite;
            $overwrite = fread($fp, $insertLength);
            fseek($fp, $i * $insertLength);
            $i++;
        } while (ftell($fp) < $combinedLength);

        fclose($fp);
    }

    public static function getTableFilePath(string $table): string
    {
        return sprintf(
            '%s%s%s.csv',
            storage_path(Settings::get('storage', 'datamigrator')),
            DIRECTORY_SEPARATOR,
            $table
        );
    }
}
