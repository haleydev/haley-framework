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

    /**
     * @return bool
     */
    public static function contains(string $string, string $search)
    {
        return str_contains($string, $search);
    }

    /**
     * @return bool
     */
    public static function start(string $string, string $search)
    {
        return str_starts_with($string, $search);
    }

    /**
     * @return bool
     */
    public static function end(string $string, string $search)
    {
        return str_ends_with($string, $search);
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

}
