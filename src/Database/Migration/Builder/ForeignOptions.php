<?php

namespace Haley\Database\Migration\Builder;

class ForeignOptions
{
    private string|null $driver = null;

    public function __construct(string $driver)
    {
        $this->driver = $driver;
    }

    public function onDelete(string $value = 'CASCADE')
    {
        $key = array_key_last(BuilderMemory::$foreign);

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            BuilderMemory::$foreign[$key]['on_delete'] = ' ON DELETE ' . $value;
        }

        return $this;
    }

    public function onUpdate(string $value = 'CASCADE')
    {
        $key = array_key_last(BuilderMemory::$foreign);

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            BuilderMemory::$foreign[$key]['on_update'] = ' ON UPDATE ' . $value;
        }

        return $this;
    }

    public function name(string $value)
    {
        $key = array_key_last(BuilderMemory::$foreign);

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            BuilderMemory::$foreign[$key]['name'] = $value;
        }

        return $this;
    }
}
