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
    public static array $foreigns = [];
    public static array $constraints = [];
    public static array $rename = [];

    public static function addColumn(string $name, string $type, int|string|array|null $paramns = null)
    {
        if (in_array(self::$driver, ['mysql', 'pgsql', 'mariadb'])) {
            if ($paramns) $type .= "($paramns)";

            self::$columns[] = [
                'name' => $name,
                'type' => $type,
                'query' => "[CL:NAME] [CL:TYPE] [OP:UNIQUE] [OP:DEFAULT] [OP:NOT_NULL] [OP:COMMENT]"
            ];
        }
    }

    static public function getColumns()
    {
        $columns = [];       

        if (!empty(self::$columns)) {
            foreach (self::$columns as $key => $value) {
                if(in_array($value['name'],self::$rename)) continue;

                $columns[$key] = $value;
                $columns[$key]['query'] = str_replace([' [OP:UNIQUE]', ' [OP:DEFAULT]', ' [OP:NOT_NULL]', ' [OP:COMMENT]'], '', $value['query']);               
            }
        }    
        
        

        return $columns;
    }
}
