<?php

namespace Haley\Collections;

class Memory
{
    private static array $memories = [];

    public static function get(int|string $key)
    {     
        if (array_key_exists($key,self::$memories)) return self::$memories[$key];        

        return null;
    }

    public static function set(int|string $key, mixed $value)
    {   
        self::$memories[$key] = $value;
    }

    public static function replace(int|string $key, mixed $values)
    {
        if (array_key_exists($key,self::$memories)) {
            $original = self::$memories[$key];

            if (is_array($original) and is_array($values)) {
                $replace = array_replace($original, $values);
            } elseif (is_object($original) and is_object($values)) {
                $replace = (object)array_replace((array)$original, (array)$values);
            } else {
                $replace = $values;
            }

            self::$memories[$key] = $replace;

            return true;
        }

        return false;
    }

    public static function delete(string $key)
    {
        if (isset(self::$memories[$key])) {
            unset(self::$memories[$key]);
            return true;
        }

        return false;
    }

    public static function isset(int|string $key)
    {
        return array_key_exists($key, (array)self::$memories);
    }
}
