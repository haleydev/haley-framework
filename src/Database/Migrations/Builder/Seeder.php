<?php
namespace Haley\Database\Migrations\Builder;

class Seeder
{
    public function definitions(string $table, string $connection = 'default')
    {
        MigrationMemory::$definitions = [
            'table' => $table,
            'connection' => $connection
        ];
    }

    public function values(array $values)
    {
        MigrationMemory::seeder($values);
    }
}
