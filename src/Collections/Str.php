<?php
namespace Haley\Collections;

class Str
{
    /**
     * @return string
     */
    public static function clearString(string $string, bool $numbers = false, string $separator = ' ')
    {
        $numbers ? $pattern = "/[^a-zA-Z0-9\s]/" : $pattern = "/[^a-zA-Z\s]/";
        $string = trim(preg_replace($pattern, '', $string));
        $string = preg_replace('/( ){2,}/', '$1', $string);

        return str_replace(' ', $separator, $string);
    }

    /**
     * @return string
     */
    public static function slug(string $string, string $separator = '-')
    {  
        $string = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $string));
        $string = preg_replace('/( ){2,}/', '$1', $string);
     
        return strtolower(str_replace(' ', $separator, $string));
    }

    /**
     * @return string
     */
    public static function camel(string $string)
    {
        return ucwords($string);
    }

    /**
     * @return string|int
     */
    public static function numbers(string $string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }
}
