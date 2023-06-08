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
        if (!empty($values[0]))  if (!is_array($values[0]))  $values = [$values];

        foreach ($values as $key_one => $value_one) {
            foreach ($value_one as $key_two => $value_two) {
                if (!in_array($key_two, static::$columns)) unset($values[$key_one][$key_two]);
            }

            if (!count($values[$key_one])) unset($values[$key_one]);
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
        return (new DB)->table(static::$table)->all();
    }

    public static function count()
    {
        return (new DB)->table(static::$table)->count();
    }
}
