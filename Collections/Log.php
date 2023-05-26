<?php
namespace Core\Collections;

class Log
{
    /**
     * Register new log
     * @return bool
     */
    public static function create(string $name,string $value)
    {
        createDir(directoryRoot('storage/logs'));

        $file = directoryRoot("storage/logs/$name.log");
        $value = '[' . date('d/m/Y h:i:s') . '] ' . $value . PHP_EOL;
            
        return file_put_contents($file, $value , FILE_APPEND);       
    }

    /**
     * Clean log file
     * @return bool
     */
    public static function clean(string $name)
    {
        $file = directoryRoot(directoryRoot("storage/logs/$name.log"));        
        
        return file_put_contents($file,'');
    }
}