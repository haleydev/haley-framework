<?php

namespace Haley\Database\Migrations\Builder;

use Haley\Collections\Config;

class Table
{
    public static function definitions(string $table, string|null $connection = null)
    {
        if ($connection == null) $connection = Config::database('default');

        MigrationMemory::$definitions = [
            'table' => $table,
            'connection' => $connection
        ];
    }

    /**
     * Drop column
     */
    public function dropColumn(string|array $column)
    {
        if (is_array($column)) {
            foreach ($column as $value) {
                MigrationMemory::drop($value);
            }
        } else {
            MigrationMemory::drop($column);
        }
    }

    public function renameColumn(string $old, string $new)
    {
        MigrationMemory::saveRename($old, $new);
    }

    public function foreign(string $column, string $references_table, string $references_column)
    {
        MigrationMemory::foreign($column, [
            'references_table' => $references_table,
            'references_column' => $references_column,
            'on_delete' => false,
            'on_update' => false
        ]);

        return new TableForeings;
    }

    public function index(string|array $column, string $using = 'BTREE')
    {
        if (is_array($column)) {
            foreach ($column as $value) {
                MigrationMemory::index($value, $using);
            }
        } else {
            MigrationMemory::index($column, $using);
        }
    }

    /**
     * INT($size) NOT NULL AUTO_INCREMENT PRIMARY KEY
     */
    public function primary(string $name, int $size = 20)
    {
        MigrationMemory::column('primary', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function char(string $name, int $size = 255)
    {
        MigrationMemory::column('char', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function varchar(string $name, int $size = 255)
    {
        MigrationMemory::column('varchar', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function enum(string $name, array $enums)
    {
        MigrationMemory::column('enum', $name, [
            'enums' => $enums
        ]);

        return new TableOptions;
    }

    public function set(string $name, array $sets)
    {
        MigrationMemory::column('set', $name, [
            'sets' => $sets
        ]);

        return new TableOptions;
    }

    public function text(string $name)
    {
        MigrationMemory::column('text', $name);

        return new TableOptions;
    }

    public function mediumText(string $name)
    {
        MigrationMemory::column('medium_text', $name);

        return new TableOptions;
    }

    public function longText(string $name)
    {
        MigrationMemory::column('long_text', $name);

        return new TableOptions;
    }

    public function json(string $name)
    {
        MigrationMemory::column('json', $name);

        return new TableOptions;
    }

    public function boolean(string $name)
    {
        MigrationMemory::column('boolean', $name);

        return new TableOptions;
    }

    public function tinyint(string $name, int $size = 10)
    {
        MigrationMemory::column('tinyint', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function int(string $name, int $size = 11)
    {
        MigrationMemory::column('int', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function bigInt(string $name, int $size = 20)
    {
        MigrationMemory::column('big_int', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function float(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::column('float', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function double(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::column('double', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function doublePrecision(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::column('double_precision', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function decimal(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::column('decimal', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function year(string $name)
    {
        MigrationMemory::column('year', $name);

        return new TableOptions;
    }

    public function time(string $name)
    {
        MigrationMemory::column('time', $name);

        return new TableOptions;
    }

    public function date(string $name)
    {
        MigrationMemory::column('date', $name);

        return new TableOptions;
    }

    public function timestamp(string $name)
    {
        MigrationMemory::column('timestamp', $name);

        return new TableOptions;
    }

    public function updateDate(string $name = 'update_at')
    {
        MigrationMemory::column('update_at', $name);

        return new TableOptions;
    }

    public function createdDate(string $name = 'created_at')
    {
        MigrationMemory::column('created_at', $name);

        return new TableOptions;
    }

    public function raw(string $name, string $value)
    {
        MigrationMemory::column('raw', $name, $value);
    }
}
