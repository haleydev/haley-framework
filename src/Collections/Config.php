<?php

namespace Haley\Collections;

use InvalidArgumentException;

class Config
{
    private static array $configs = [];

    public static function app(string|array|null $key = null, mixed $default = null)
    {
        return self::get('app', $key, $default);
    }

    public static function database(string|array|null $key = null, mixed $default = null)
    {
        return self::get('database', $key, $default);
    }

    public static function route(string|array|null $key = null, mixed $default = null)
    {
        return self::get('route', $key, $default);
    }

    /**
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $keys = $arguments[0] ?? null;
        $default = null;

        if (array_key_exists(1, $arguments)) $default = $arguments[1];

        return self::get($name, $keys, $default);
    }

    /**
     * @return mixed
     */
    private static function get(string $name, string|array|null $keys, mixed $default)
    {
        if (!isset(self::$configs[$name])) {
            if (!file_exists(directoryRoot("config/$name.php"))) return $default;
            self::$configs[$name] = require directoryRoot("config/$name.php");
        }

        if ($keys === null) return self::$configs[$name];

        if (!is_string($keys) and !is_array($keys)) throw new InvalidArgumentException('Invalid keys: string, array or null');

        if (is_string($keys)) $keys = explode('.', $keys);

        $result = self::$configs[$name];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $result)) return $default;

            $result = $result[$key];
        }

        return $result;
    }
}
