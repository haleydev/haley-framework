<?php
namespace Haley\Collections;
use Haley\Database\Query\DB;

abstract class Model
{ 
    public static string $table;
    public static string|null $primary;  
    public static array $columns;    

    public static function create(array $values)
    {
        return (new DB)->table(self::$table)->insert($values);   
    }

    public static function query()
    {
        return (new DB)->table(static::$table);
    }

    public static function all()
    {
        return (new DB)->table(static::$table)->all();
    }

    public static function count()
    {
        return (new DB)->table(static::$table)->count();
    }
}