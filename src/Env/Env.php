<?php

namespace Haley\Env;

class Env
{
    /**
     * Array com as informações env
     */
    private static $env = [];

    /**
     * Variáveis de ambiente env
     * @return string|array|int|false|true|null
     */
    public static function env(string|null $key = null, mixed $or = null)
    {
        if (!count(self::$env)) {
            if (file_exists(directoryRoot('storage/cache/jsons/env.json'))) {
                self::$env = self::envCache();
            } elseif (file_exists(directoryRoot('.env'))) {
                self::$env = self::envRead();
            }
        }

        if ($key == null) return self::$env;

        if (array_key_exists($key, self::$env)) return self::$env[$key];

        return $or;
    }

    /**
     * @return array
     */
    private static function envCache()
    {
        return json_decode(file_get_contents(directoryRoot('storage/cache/jsons/env.json')), true);
    }

    /**
     * @return array
     */
    private static function envRead()
    {
        $file = array_filter(file(directoryRoot('.env')));
        $env = [];

        foreach ($file as $value) {
            if ($value[0] === '#') continue;

            $item = explode('=', trim($value), 2);
            isset($item[1]) ? $e = self::checkValue(trim($item[1])) : $e = null;
            $env[trim($item[0])] = $e;
        }

        return $env;
    }

    /**
     * Check value
     */
    private static function checkValue($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}
