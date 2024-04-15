<?php

namespace Haley\Collections;

use Haley\Database\Query\DB;

abstract class Model
{
    public static string $table;
    public static string|null $primary = null;
    public static array $columns = [];

    public static function create(array $values)
    {
        if (!empty($values[0])) if (!is_array($values[0])) $values = [$values];

        foreach ($values as $key => $data) {
            foreach ($data as $column => $value) {
                if (!in_array($column, static::$columns)) unset($values[$key][$column]);
            }

            if (!count($values[$key])) unset($values[$key]);
        }

        if (count($values)) return (new DB)->table(static::$table)->insert($values);

        return 0;
    }

    public static function query()
    {
        return (new DB)->table(static::$table);
    }

    public static function all()
    {
        return (new DB)->table(static::$table)->get();
    }

    public static function count()
    {
        return (new DB)->table(static::$table)->count();
    }
}
