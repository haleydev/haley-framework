<?php

namespace Haley\Collections;

class Config
{
    private static array $configs = [];

    private static function search(string $config, string|null $key = null)
    {
        if (!isset(self::$configs[$config])) {
            if (file_exists(directoryRoot("config/$config.php"))) {
                self::$configs[$config] = require directoryRoot("config/$config.php");
            } else {
                return null;
            }
        }

        if ($key === null) return self::$configs[$config];

        if (isset(self::$configs[$config][$key])) return self::$configs[$config][$key];

        return null;
    }

    public static function app(string|null $key = null, mixed $or = null)
    {
        $result = self::search('app', $key);

        if ($result === null) return $or;

        return $result;
    }

    public static function routes(string|null $key = null, mixed $or = null)
    {
        $result = self::search('route', $key);

        if ($result === null) return $or;

        return $result;
    }

    public static function database(string|null $key = null, mixed $or = null)
    {
        $result = self::search('database', $key);

        if ($result === null) return $or;

        return $result;
    }
}
