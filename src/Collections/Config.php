<?php

namespace Haley\Collections;

class Config
{
    private static array $configs = [];

    private static function search(string $config, string $key = null)
    {
        if (!isset(self::$configs[$config])) {
            if (file_exists(directoryRoot("config/$config.php"))) {
                self::$configs[$config] = require directoryRoot("config/$config.php");
            } else {
                return false;
            }
        }

        if ($key == null) return self::$configs[$config];
        if (isset(self::$configs[$config][$key])) return self::$configs[$config][$key];

        return false;
    }

    public static function app(string $key = null)
    {
        return self::search('app', $key);
    }

    public static function routes(string $key = null)
    {
        return self::search('route', $key);
    }

    public static function database(string $key = null)
    {
        return self::search('database', $key);
    }
}
