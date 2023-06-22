<?php

namespace Haley\Database\Migration\Builder;

class BuilderMemory
{
    public static string|null $driver = null;
    public static string|null $connection = null;
    public static string|null $table = null;
    public static string|null $primary = null;

    public static array $id = [];
    public static array $columns = [];
    public static array $constraints = [];
    public static array $rename = [];
    public static array $foreign = [];

    public static function addColumn(string $name, string $type, int|string|array|null $paramns = null)
    {
        if (in_array(self::$driver, ['mysql', 'pgsql', 'mariadb'])) {
            if ($paramns) $type .= "($paramns)";

            self::$columns[] = [
                'name' => $name,
                'type' => $type,
                'query' => "[CL:NAME] [CL:TYPE] [OP:DEFAULT] [OP:NOT_NULL] [OP:COMMENT]"
            ];
        }
    }

    static public function compileForeigns()
    {
        foreach (self::$foreign as $value) {
            $name = $value['name'];
            $column = $value['column'];
            $reference_table = $value['reference_table'];
            $reference_column = $value['reference_column'];
            $on_delete = $value['on_delete'];
            $on_update = $value['on_update'];

            if (in_array(self::$driver, ['mysql', 'pgsql', 'mariadb'])) {
                $value = sprintf('(%s) REFERENCES %s (%s)', $column, $reference_table, $reference_column);
                if ($on_delete !== null) $value .= $on_delete;
                if ($on_update !== null) $value .= $on_update;
                if ($name == null) $name = sprintf('foreign_%s_%s_%s_%s', self::$table, $column, $reference_table, $reference_column);

                self::addConstraint($name, 'FOREIGN KEY', $value);
            }
        }
    }

    static public function getColumns()
    {
        $columns = [];

        if (!empty(self::$columns)) {
            foreach (self::$columns as $key => $value) {
                if (in_array($value['name'], self::$rename)) continue;

                $columns[$key] = $value;
                $columns[$key]['query'] = str_replace([' [OP:DEFAULT]', ' [OP:NOT_NULL]', ' [OP:COMMENT]'], '', $value['query']);
            }
        }

        return $columns;
    }

    static public function addConstraint(string $name, string $type, string $value)
    {
        self::$constraints[] = [
            'name' => $name,
            'type' => $type,
            'value' => $value
        ];
    }
}
