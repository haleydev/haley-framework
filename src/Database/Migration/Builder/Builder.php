<?php

namespace Haley\Database\Migration\Builder;

use Haley\Collections\Log;
use InvalidArgumentException;

class Builder
{
    public function id(string $name = 'id', string|null $comment = null)
    {
        if (!empty(BuilderMemory::$primary) or count(BuilderMemory::$id)) {
            Log::create('migration', 'Table ' . BuilderMemory::$table . ' must have only one primary key');
            throw new InvalidArgumentException('Table ' . BuilderMemory::$table . ' must have only one primary key');
        }

        BuilderMemory::$id = [
            'name' => $name,
            'comment' => $comment
        ];
    }

    public function varchar(string $name, int $size = 255)
    {
        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = sprintf('varchar(%s)', $size);
        } else {
            return $this->typeError('varchar');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions(BuilderMemory::$config['driver']);
    }

    public function text(string $name)
    {
        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = 'text';
        } else {
            return $this->typeError('varchar');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions(BuilderMemory::$config['driver']);
    }

    public function json(string $name)
    {
        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = 'json';
        } else {
            return $this->typeError('varchar');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions(BuilderMemory::$config['driver']);
    }

    public function int(string $name, int|null $size = null)
    {
        if (in_array(BuilderMemory::$config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = sprintf($size !== null ? 'INT(%s)' : 'INT', $size);
        } else {
            return $this->typeError('int');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions(BuilderMemory::$config['driver']);
    }

    public function dropConstrant()
    {
    }

    public function dropColumn()
    {
    }

    public function foreign(string $column, string $reference_table, string $reference_column)
    {
        BuilderMemory::$foreign[] = [
            'column' => $column,
            'reference_table' => $reference_table,
            'reference_column' => $reference_column,
            'name' => null,
            'on_delete' => null,
            'on_update' => null
        ];

        return new ForeignOptions(BuilderMemory::$config['driver']);
    }

    public function rename(string $column, string $to)
    {
        BuilderMemory::$rename[$column] = $to;
    }

    private function typeError(string $type)
    {
        Log::create('migration', 'Driver does not support the type ' . $type);
        throw new InvalidArgumentException('Driver does not support the type ' . $type);
    }
}
