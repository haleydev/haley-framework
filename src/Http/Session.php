<?php

namespace Haley\Http;

class Session
{
    public static function set(int|string $key, $values = true)
    {
        if ($key === 'HALEY') return false;

        $_SESSION[$key] = $values;

        return true;
    }

    public static function replace(int|string $key, $values)
    {
        if (array_key_exists($key, $_SESSION) and $key !== 'HALEY') {
            $original = $_SESSION[$key];

            if (is_array($original) and is_array($values)) {
                $replace = array_replace($original, $values);
            } elseif (is_object($original) and is_object($values)) {
                $replace = (object)array_replace((array)$original, (array)$values);
            } else {
                $replace = $values;
            }

            $_SESSION[$key] = $replace;

            return $replace;
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function unset(int|string $key)
    {
        if (array_key_exists($key, $_SESSION) and $key !== 'HALEY') {
            unset($_SESSION[$key]);
            return true;
        }

        return false;
    }

    /**
     * Return session
     */
    public static function get(string $key)
    {
        if (array_key_exists($key, $_SESSION) and $key !== 'HALEY') return $_SESSION[$key];

        return null;
    }

    /**
     * Check isset session
     * @return bool
     */
    public static function has(string $key)
    {
        if (array_key_exists($key, $_SESSION) and $key !== 'HALEY') return true;

        return false;
    }

    public static function status()
    {
        return session_status();
    }

    public static function id(null|string $id = null)
    {
        return session_id($id);
    }

    public static function expire()
    {
        return session_cache_expire();
    }

    public static function destroy()
    {
        return session_destroy();
    }
}
