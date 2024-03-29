<?php

namespace Haley\Database\Migration\Builder;

use Haley\Collections\Log;
use InvalidArgumentException;

class BuilderOptions
{
    public function primary()
    {
        $key = array_key_last(BuilderMemory::$columns);

        if (!empty(BuilderMemory::$primary) or count(BuilderMemory::$id)) {
            Log::create('migration', 'Table ' . BuilderMemory::$table . ' must have only one primary key');
            throw new InvalidArgumentException('Table ' . BuilderMemory::$table . ' must have only one primary key');
        }

        BuilderMemory::$primary = BuilderMemory::$columns[$key]['name'];

        return $this;
    }

    public function comment(string $value)
    {
        $key = array_key_last(BuilderMemory::$columns);

        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            BuilderMemory::$columns[$key]['query'] = str_replace('[OP:COMMENT]', "COMMENT '{$value}'", BuilderMemory::$columns[$key]['query']);
        }

        return $this;
    }

    public function nullable(bool $value = true)
    {
        $key = array_key_last(BuilderMemory::$columns);

        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            BuilderMemory::$columns[$key]['query'] = str_replace('[OP:NOT_NULL]', $value ? 'NULL' : 'NOT NULL', BuilderMemory::$columns[$key]['query']);
        }

        return $this;
    }

    public function default(string $value, bool $raw = false)
    {
        $key = array_key_last(BuilderMemory::$columns);

        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            if (!$raw) $value = "'$value'";

            BuilderMemory::$columns[$key]['query'] = str_replace('[OP:DEFAULT]', 'DEFAULT ' . $value, BuilderMemory::$columns[$key]['query']);
        }

        return $this;
    }

    public function unique(string|null $name = null)
    {
        $key = array_key_last(BuilderMemory::$columns);

        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $column = BuilderMemory::$columns[$key]['name'];

            if ($name == null) $name = 'unique_' . BuilderMemory::$table . '_' . $column;

            BuilderMemory::addConstraint($name, 'UNIQUE', "($column)");
        }

        return $this;
    }
}
