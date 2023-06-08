<?php
namespace Haley\Database\Migrations\Builder;

class MigrationMemory
{
    public static array $definitions = [];
    public static array $messages = [];
    public static array $columns = [];
    public static array $options = [];
    public static array $drops = [];
    public static array $renames = [];
    public static array $foreigns = [];
    public static array $indexes = [];
    public static array $positions = [];
    public static array $seeders = [];

    public static string $last_column;

    public static function column(string $type, string $name, $values = null)
    {
        if (!isset(self::$columns[$type])) {
            self::$columns[$type] = [];
        }

        self::$last_column = $name;
        self::$columns[$type][$name] = $values;
        self::positions($name);
    }

    private static function positions(string $column)
    {
        self::$positions[] = $column;
    }

    public static function drop($column)
    {
        self::$drops[] = $column;

        $id = array_search($column, self::$positions);

        if (is_numeric($id)) {
            $positions = self::$positions;

            if (isset($positions[$id])) {
                if ($positions[$id] == $column) {
                    unset($positions[$id]);
                    self::$positions = array_values($positions);
                }
            }
        }
    }

    public static function saveRename(string $old, string $new)
    {
        self::$renames[$old] = $new;
    }

    public static function option(string $type, $values = null)
    {
        if (!isset(self::$options[$type])) {
            self::$options[$type] = [];
        }

        self::$options[$type][self::$last_column] = $values;
    }

    public static function foreign(string $foreign, $values)
    {
        self::$foreigns[$foreign] = $values;
    }

    public static function index(string $index, string $using)
    {
        self::$indexes[$index] = [
            'using' => $using
        ];
    }

    public static function message(string $mesage, bool $error = false, bool $reset = false)
    {
        if(!isset(self::$messages[self::$definitions['migration']])){
            self::$messages[self::$definitions['migration']] = [];
        }

        if ($reset == true) {
            unset( self::$messages[self::$definitions['migration']]);      
        }

        self::$messages[self::$definitions['migration']][] = [
            'error' => $error,
            'mesage' => $mesage,
            'table' => self::$definitions['table']           
        ];
    }

    public static function seeder(array $values)
    {
        self::$seeders = $values;       
    }

    public static function reset()
    {
        self::$last_column = '';
        self::$definitions = [];
        // self::$messages = [];        
        self::$columns = [];
        self::$options = [];
        self::$drops = [];
        self::$renames = [];
        self::$foreigns = [];
        self::$indexes = [];
        self::$positions = [];
        self::$seeders = [];
    }
}
