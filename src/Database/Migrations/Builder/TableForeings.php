<?php
namespace Core\Database\Migrations\Builder;

class TableForeings
{
    public function onUpdate(string $clause = 'CASCADE')
    {
        $key = array_key_last(MigrationMemory::$foreigns);
        MigrationMemory::$foreigns[$key]['on_update'] = $clause;

        return $this;
    }

    public function onDelete(string $clause = 'CASCADE')
    {
        $key = array_key_last(MigrationMemory::$foreigns);
        MigrationMemory::$foreigns[$key]['on_delete'] = $clause;

        return $this;
    }
}
