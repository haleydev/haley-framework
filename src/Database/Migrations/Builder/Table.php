<?php
namespace Haley\Database\Migrations\Builder;

class Table
{
    public static function definitions(string $table, string $connection = 'default')
    {
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
                MigrationMemory::saveDrop($value);
            }
        } else {
            MigrationMemory::saveDrop($column);
        }
    }

    public function renameColumn(string $old, string $new)
    {
        MigrationMemory::saveRename($old, $new);
    }

    public function foreign(string $column, string $references_table, string $references_column)
    {
        MigrationMemory::saveForeign($column, [
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
                MigrationMemory::saveIndex($value, $using);
            }
        } else {
            MigrationMemory::saveIndex($column, $using);
        }
    }

    /**
     * INT($size) NOT NULL AUTO_INCREMENT PRIMARY KEY
     */
    public function primary(string $name, int $size = 20)
    {
        MigrationMemory::saveColumn('primary', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function char(string $name, int $size = 255)
    {
        MigrationMemory::saveColumn('char', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function varchar(string $name, int $size = 255)
    {
        MigrationMemory::saveColumn('varchar', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function enum(string $name, array $enums)
    {
        MigrationMemory::saveColumn('enum', $name, [
            'enums' => $enums
        ]);

        return new TableOptions;
    }

    public function set(string $name, array $sets)
    {
        MigrationMemory::saveColumn('set', $name, [
            'sets' => $sets
        ]);

        return new TableOptions;
    }

    public function text(string $name)
    {
        MigrationMemory::saveColumn('text', $name);

        return new TableOptions;
    }

    public function mediumText(string $name)
    {
        MigrationMemory::saveColumn('medium_text', $name);

        return new TableOptions;
    }

    public function longText(string $name)
    {
        MigrationMemory::saveColumn('long_text', $name);

        return new TableOptions;
    }

    public function json(string $name)
    {
        MigrationMemory::saveColumn('json', $name);

        return new TableOptions;
    }

    public function boolean(string $name)
    {
        MigrationMemory::saveColumn('boolean', $name);

        return new TableOptions;
    }

    public function tinyint(string $name, int $size = 10)
    {
        MigrationMemory::saveColumn('tinyint', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function int(string $name, int $size = 11)
    {
        MigrationMemory::saveColumn('int', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function bigInt(string $name, int $size = 20)
    {
        MigrationMemory::saveColumn('big_int', $name, [
            'size' => $size
        ]);

        return new TableOptions;
    }

    public function float(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::saveColumn('float', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function double(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::saveColumn('double', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function doublePrecision(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::saveColumn('double_precision', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function decimal(string $name, int $size = 11, int $precision = 2)
    {
        MigrationMemory::saveColumn('decimal', $name, [
            'size' => $size,
            'precision' => $precision
        ]);

        return new TableOptions;
    }

    public function year(string $name)
    {
        MigrationMemory::saveColumn('year', $name);

        return new TableOptions;
    }

    public function time(string $name)
    {
        MigrationMemory::saveColumn('time', $name);

        return new TableOptions;
    }

    public function date(string $name)
    {
        MigrationMemory::saveColumn('date', $name);

        return new TableOptions;
    }

    public function timestamp(string $name)
    {
        MigrationMemory::saveColumn('timestamp', $name);

        return new TableOptions;
    }

    public function updateDate(string $name = 'update_at')
    {
        MigrationMemory::saveColumn('update_at', $name);

        return new TableOptions;
    }

    public function createdDate(string $name = 'created_at')
    {
        MigrationMemory::saveColumn('created_at', $name);

        return new TableOptions;
    }

    public function raw(string $name, string $value)
    {
        MigrationMemory::saveColumn('raw', $name, $value);
    }
}
