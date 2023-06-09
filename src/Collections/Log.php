<?php

namespace Haley\Collections;

class Log
{
    /**
     * Register new log
     * @return bool
     */
    public static function create(string $name, string $value)
    {
        createDir(directoryRoot('storage/logs'));

        $file = directoryRoot("storage/logs/$name.log");
        $value = '[' . date('d/m/Y H:i:s') . '] ' . $value . PHP_EOL;
        return file_put_contents($file, $value, FILE_APPEND);
    }

    /**
     * Clean log file
     * @return bool
     */
    public static function clean(string|array $logs)
    {
        if (is_string($logs)) $logs = [$logs];

        $response = false;

        foreach ($logs as $log) {
            $file = directoryRoot("storage/logs/$log.log");

            if (!file_exists($file)) continue;
            if (file_put_contents($file, '') !== false) $response = true;;

            $response = false;
        }

        return $response;
    }
}
