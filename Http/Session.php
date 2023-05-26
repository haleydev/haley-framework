<?php
namespace Core\Http;

class Session
{
    public static function create(int|string $key,$values = true)
    {
        $_SESSION[$key] = $values;
    }

    public static function replace(int|string $key, $values)
    {
        if(array_key_exists($key,$_SESSION)) {
            $original = $_SESSION[$key];  

            if(is_array($original) and is_array($values)) {
                $replace = array_replace($original, $values);                
            }elseif(is_object($original) and is_object($values)){
                $replace = (object)array_replace((array)$original, (array)$values);            
            }else{
                $replace = $values;
            } 

            $_SESSION[$key] = $replace;

            return $replace;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function delete(int|string $key)
    {
        if(array_key_exists($key,$_SESSION) and $key != 'FRAMEWORK') {
            unset($_SESSION[$key]);
            return true;
        }

        return false;
    }

    /**
     * Return session or false
     */
    public static function get(string $key)
    {
        if(array_key_exists($key,$_SESSION)) {
            return $_SESSION[$key];
        }

        return false;
    }

    /**
     * Check isset session
     * @return bool
     */
    public static function isset(string $key)
    {
        if(array_key_exists($key,$_SESSION)) {
            return true;
        }

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
}