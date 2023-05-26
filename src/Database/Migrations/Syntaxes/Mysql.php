<?php
namespace Haley\Database\Migrations\Syntaxes;

use Core\Collections\Config;
use Core\Collections\Log;
use Core\Database\Connection;
use Core\Database\Migrations\Builder\MigrationMemory;
use Core\Database\Query\DB;
use PDO;
use PDOException;

class Mysql
{
    private bool $new = true;

    private string $table;
    private string $connection;   

    private array $columns = [];
    private array $db_columns = [];
    private array $db_foreigns = [];
    private array $db_indexes = [];

    public function migrate()
    {
        try {
            $this->table = MigrationMemory::$definitions['table'];
            $this->connection = MigrationMemory::$definitions['connection'];            

            if ($this->issetTable($this->table, $this->connection)) {
                $this->db_columns = $this->dbColumns($this->table, $this->connection);
                $this->new = false;
            }
         
            $this->columns(MigrationMemory::$columns);
            $this->options(MigrationMemory::$options);

            if ($this->new == true) {
                $this->createTable();
            } else {
                $this->db_foreigns = $this->dbForeigns($this->table, $this->connection);
                $this->db_indexes = $this->dbIndexes($this->table, $this->connection);

                $this->dropForeigns();
                $this->dropIndexes();
                $this->dropColumns();
                $this->alterTable();

                $this->changes();
            }
        } catch (PDOException $error) {
            MigrationMemory::message($error->getMessage(), true, true);
            Log::create('migration', $error->getMessage());
        }
    }

    public function issetTable(string $table, string $connection = 'default')
    {
        $query = DB::query('SHOW TABLES LIKE ?', [$table], $connection)->fetch(PDO::FETCH_OBJ);

        if (!empty($query)) {
            return true;
        }

        return false;
    }

    public function dbColumns(string $table, string $connection = 'default')
    {
        $columns = DB::query("SHOW FULL COLUMNS FROM {$table}", [], $connection)->fetchAll(PDO::FETCH_ASSOC);
        $db_columns = [];

        if ($columns and count($columns) > 0) {
            foreach ($columns as $value) {
                $db_columns[$value['Field']] = $value;
            }
        }

        return $db_columns;
    }

    public function dbIndexes(string $table, string $connection = 'default')
    {
        $indexes = DB::query("SHOW INDEX FROM {$table} WHERE Key_name != 'PRIMARY'", connection: $connection)->fetchAll(PDO::FETCH_ASSOC);
        $db_indexes = [];

        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                $db_indexes[$index['Key_name']] = $index;
            }
        }

        return $db_indexes;
    }

    public function dbForeigns(string $table, string $connection = 'default')
    {
        if(!empty(Config::database('connections')[$connection])) {
            $database = (string)Config::database('connections')[$connection]['database'];
        }else {
            $database = '';
        }
        
        // dd($database);die;

        $foreings = DB::query("SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '{$database}' AND REFERENCED_COLUMN_NAME IS NOT NULL AND TABLE_NAME = '{$table}'")->fetchAll(PDO::FETCH_ASSOC);


        // $foreings = DB::query('SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = ? AND CONSTRAINT_TYPE = ?', [$table, 'FOREIGN KEY'], $connection)->fetchAll(PDO::FETCH_ASSOC);
        $db_foreigns = [];

        if (!empty($foreings)) {
            foreach ($foreings as $foreing) {
                $db_foreigns[$foreing['CONSTRAINT_NAME']] = $foreing;
            }
        }

        return $db_foreigns;
    }

    /**
     * @return string|true 
     */
    public function dropTable(string $table,string $connection = 'default')
    {
        if($this->issetTable($table,$connection)){           
            try{
                DB::query("DROP TABLE $table",connection:$connection);
            } catch (PDOException $error) {
                return $error->getMessage();
            }

            if(!$this->issetTable($table,$connection)) {
                return true;
            }
        }

        return "table $table does not exist";
    }

    public function getDatabaseInfo(string $connection = 'default') 
    {
        $result = [];

        $tables = DB::query('SHOW TABLES',connection: $connection)->fetchAll(PDO::FETCH_OBJ);   
        
        if(empty($tables) or count((array)$tables) == 0 ) {
            return false;
        }

        foreach($tables as $value) {          
            $table = array_values((array)$value)[0];      
            $primary = DB::query("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'", connection: $connection)->fetch(PDO::FETCH_OBJ);    
            $db_columns = $this->dbColumns($table,$connection); 

            !empty($primary) ? $primary = $primary->Column_name : $primary = null;
       
            $columns = [];

            foreach($db_columns as $value){
                $columns[] = $value['Field'];                
            }

            $result[] = [
                'table' => $table,
                'primary' => $primary,
                'columns' => $columns
            ];
        }  
        
        return !empty($result) ? $result : false;
    }

    private function dropForeigns()
    {
        if (count($this->db_foreigns) > 0) {
            DB::query('SET FOREIGN_KEY_CHECKS=0', connection: $this->connection);

            foreach ($this->db_foreigns as $value) {
                DB::query("ALTER TABLE {$this->table} DROP FOREIGN KEY `{$value['CONSTRAINT_NAME']}`", connection: $this->connection);
            }

            DB::query('SET FOREIGN_KEY_CHECKS=1', connection: $this->connection);
        }     
    }

    private function dropIndexes()
    {
        if (count($this->db_indexes) > 0) {
            foreach ($this->db_indexes as $index) {
                DB::query("ALTER TABLE {$this->table} DROP INDEX {$index['Key_name']}", connection: $this->connection);
            }
        }
    }

    private function dropColumns()
    {
        $columns = MigrationMemory::$drops;

        foreach ($columns as $column) {
            if (isset($this->db_columns[$column])) {
                DB::query("ALTER TABLE {$this->table} DROP `$column`", connection: $this->connection);
                MigrationMemory::message("dropped column $column");
            }
        }
    }

    private function createTable()
    {
        if (count($this->columns) > 0) {
            $columns = '';
            $definitions = '';

            foreach (MigrationMemory::$positions as $position => $column) {
                if (isset($this->columns[$column])) {
                    $columns .= $this->columns[$column] . ',';
                }
            }

            $config = Connection::getConfig($this->connection);

            if (isset($config['engine'])) {
                $definitions .= " ENGINE = {$config['engine']}";
            }

            if (isset($config['charset'])) {
                $definitions .= " DEFAULT CHARSET = {$config['charset']}";
            }

            if (isset($config['collate'])) {
                $definitions .= " COLLATE = {$config['collate']}";
            }

            $columns = trim($columns, ',');
            $definitions = trim($definitions);

            $query = "CREATE TABLE {$this->table} ({$columns}) {$definitions};";
            DB::query($query, connection: $this->connection);    
            
            $updates = $this->dbColumns($this->table, $this->connection);

            // ADD FOREIGNS
            foreach (MigrationMemory::$foreigns as $name => $values) {
                if (!in_array($name, MigrationMemory::$drops) and isset($updates[$name])) {

                    $foreign = "CONSTRAINT `FK_{$name}_{$this->table}` FOREIGN KEY (`{$name}`) REFERENCES `{$values['references_table']}`(`{$values['references_column']}`)";

                    if (!empty($values['on_delete'])) {
                        $foreign .= " ON DELETE {$values['on_delete']}";
                    }

                    if (!empty($values['on_update'])) {
                        $foreign .= " ON UPDATE {$values['on_update']}";
                    }

                    DB::query("ALTER TABLE {$this->table} ADD {$foreign}", connection: $this->connection);
                }
            }

            // ADD INDEXES
            foreach (MigrationMemory::$indexes as $index => $values) {
                if (!in_array($index, MigrationMemory::$drops) and isset($updates[$index])) {
                    DB::query("ALTER TABLE {$this->table} ADD INDEX (`{$index}`) USING {$values['using']}", connection: $this->connection);
                }
            }

            MigrationMemory::message("table added to database");
        }
    }

    private function alterTable()
    {
       
        if (count($this->columns) > 0) {
            $columns = '';

            foreach (MigrationMemory::$positions as $position => $column) {
                if (isset($this->columns[$column])) {
                    if ($position == 0) {
                        $columns .= "{$this->columns[$column]} first;";
                    } else {
                        $last = MigrationMemory::$positions[$position - 1];
                        $columns .= "{$this->columns[$column]} after `{$last}`;";
                    }
                }
            }           

            DB::query($columns, connection: $this->connection);
        }

        $updates = $this->dbColumns($this->table, $this->connection);

        // ADD FOREIGNS
        foreach (MigrationMemory::$foreigns as $name => $values) {
            if (!in_array($name, MigrationMemory::$drops) and isset($updates[$name])) {

                $foreign = "CONSTRAINT `FK_{$name}_{$this->table}` FOREIGN KEY (`{$name}`) REFERENCES `{$values['references_table']}`(`{$values['references_column']}`)";

                if (!empty($values['on_delete'])) {
                    $foreign .= " ON DELETE {$values['on_delete']}";
                }

                if (!empty($values['on_update'])) {
                    $foreign .= " ON UPDATE {$values['on_update']}";
                }

                DB::query("ALTER TABLE {$this->table} ADD {$foreign}", connection: $this->connection);
            }
        }

        // ADD INDEXES
        foreach (MigrationMemory::$indexes as $index => $values) {
            if (!in_array($index, MigrationMemory::$drops) and isset($updates[$index])) {
                DB::query("ALTER TABLE {$this->table} ADD INDEX (`{$index}`) USING {$values['using']}", connection: $this->connection);
            }
        }
    }

    private function changes()
    {
        // CHECK COLUMNS CHANGES
        $old_columns = $this->db_columns;
        $new_columns = $this->dbColumns($this->table, $this->connection);

        foreach ($old_columns as $column => $values) {
            if (isset($new_columns[$column])) {
                $changes = array_diff_assoc($new_columns[$column], $old_columns[$column]);

                if (count($changes) > 0) {
                    MigrationMemory::message("modified column {$column}");
                }
            }
        }

        // CHECK FOREINGS CHANGES
        $old_foreigns = $this->db_foreigns;
        $new_foreigns = $this->dbForeigns($this->table, $this->connection);

        foreach ($new_foreigns as $foreign => $values) {
            $name = str_replace('FK_', '', $foreign);

            if (isset($old_foreigns[$foreign])) {
                $changes = array_diff_assoc($new_foreigns[$foreign], $old_foreigns[$foreign]);

                if (count($changes) > 0) {
                    MigrationMemory::message("modified foreign key {$name}");
                }
            } else {
                MigrationMemory::message("added foreign key {$name}");
            }
        }

        foreach ($old_foreigns as $foreign => $values) {
            $name = str_replace('FK_', '', $foreign);

            if (!isset($new_foreigns[$foreign])) {
                MigrationMemory::message("removed foreign key {$name}");
            }
        }

        // CHECK INDEXES CHANGES
        $old_indexes = $this->db_indexes;
        $new_indexes = $this->dbIndexes($this->table, $this->connection);

        foreach ($new_indexes as $index => $values) {
            if (isset($old_indexes[$index])) {
                $changes = array_diff_assoc($new_indexes[$index], $old_indexes[$index]);

                if (count($changes) > 0) {
                    MigrationMemory::message("modified index {$index}");
                }
            } else {
                MigrationMemory::message("added index {$index}");
            }
        }

        foreach ($old_indexes as $index => $values) {
            if (!isset($new_indexes[$index])) {
                MigrationMemory::message("removed index {$index}");
            }
        }
    }

    private function addColumn(string $name, string $value)
    {
        if (in_array($name, MigrationMemory::$drops)) {
            return;
        }

        if ($this->new == true) {
            $this->columns[$name] = "`{$name}` $value";
            return;
        }

        $renames = MigrationMemory::$renames;
        $rename = array_search($name, $renames);

        if (isset($this->db_columns[$rename])) {
            $new = $renames[$rename];
            $this->columns[$name] = "ALTER TABLE {$this->table} CHANGE `{$rename}` `{$new}` {$value}";
            MigrationMemory::message("renamed column {$rename} to {$new}");
            return;
        }

        if (isset($this->db_columns[$name])) {
            $this->columns[$name] = "ALTER TABLE {$this->table} CHANGE `{$name}` `{$name}` {$value}";
            return;
        }

        $this->columns[$name] = "ALTER TABLE {$this->table} ADD COLUMN `{$name}` {$value}";
        MigrationMemory::message("added column {$name}");
    }

    private function options(array $options)
    {
        if (isset($options['unique'])) {
            foreach ($options['unique'] as $column => $value) {
                $this->addOption($column, 'unique');
            }
        }

        if (isset($options['default'])) {
            foreach ($options['default'] as $column => $value) {
                $this->addOption($column, "default '{$value['default']}'");
            }
        }

        if (isset($options['not_null'])) {
            foreach ($options['not_null'] as $column => $value) {
                $this->addOption($column, 'not null');
            }
        }

        if (isset($options['comment'])) {
            foreach ($options['comment'] as $column => $value) {
                $this->addOption($column, "comment '{$value['comment']}'");
            }
        }
    }

    private function addOption(string $column, $value)
    {
        if (isset($this->columns[$column])) {
            $this->columns[$column] .= ' ' . $value;
        }
    }

    private function quotes(array|string $values, string $quote = "'")
    {
        if (is_string($values)) {
            return $quote . $values . $quote;
        }        

        foreach ($values as $key => $value) {
            $values[$key] = $quote . $value . $quote;
        }

        return $values;
    }

    private function columns(array $columns)
    {
        if (isset($columns['primary'])) {
            foreach ($columns['primary'] as $name => $value) {
                if ($this->new == false) {
                    $primary = DB::query("SHOW KEYS FROM {$this->table} WHERE Key_name = 'PRIMARY'", connection: $this->connection)->fetch(PDO::FETCH_OBJ);
                } else {
                    $primary = false;
                }

                if (!empty($primary)) {
                    if ($primary->Column_name != $name) {
                        DB::query("ALTER TABLE {$this->table} MODIFY {$primary->Column_name} int({$value['size']})", connection: $this->connection);
                        DB::query("ALTER TABLE {$this->table} DROP PRIMARY KEY", connection: $this->connection);
                        $this->addColumn($name, "int({$value['size']}) NOT NULL AUTO_INCREMENT PRIMARY KEY");
                    }
                } else {
                    $this->addColumn($name, "int({$value['size']}) NOT NULL AUTO_INCREMENT PRIMARY KEY");
                }
            }
        }

        if (isset($columns['char'])) {
            foreach ($columns['char'] as $name => $value) {
                $this->addColumn($name, "char({$value['size']})");
            }
        }

        if (isset($columns['varchar'])) {
            foreach ($columns['varchar'] as $name => $value) {
                $this->addColumn($name, "varchar({$value['size']})");
            }
        }

        if (isset($columns['enum'])) {
            foreach ($columns['enum'] as $name => $value) {
                $enums = implode(',', $this->quotes($value['enums']));
                $this->addColumn($name, "enum({$enums})");
            }
        }

        if (isset($columns['set'])) {
            foreach ($columns['set'] as $name => $value) {
                $sets = implode(',', $this->quotes($value['sets']));
                $this->addColumn($name, "set({$sets})");
            }
        }

        if (isset($columns['text'])) {
            foreach ($columns['text'] as $name => $value) {
                $this->addColumn($name, 'text');
            }
        }

        if (isset($columns['medium_text'])) {
            foreach ($columns['medium_text'] as $name => $value) {
                $this->addColumn($name, 'mediumtext');
            }
        }

        if (isset($columns['long_text'])) {
            foreach ($columns['long_text'] as $name => $value) {
                $this->addColumn($name, 'longtext');
            }
        }

        if (isset($columns['json'])) {
            foreach ($columns['json'] as $name => $value) {
                $this->addColumn($name, 'json');
            }
        }

        if (isset($columns['boolean'])) {
            foreach ($columns['boolean'] as $name => $value) {
                $this->addColumn($name, 'boolean');
            }
        }

        if (isset($columns['tinyint'])) {
            foreach ($columns['tinyint'] as $name => $value) {
                $this->addColumn($name, "tinyint({$value['size']})");
            }
        }

        if (isset($columns['int'])) {
            foreach ($columns['int'] as $name => $value) {
                $this->addColumn($name, "int({$value['size']})");
            }
        }

        if (isset($columns['big_int'])) {
            foreach ($columns['big_int'] as $name => $value) {
                $this->addColumn($name, "bigint({$value['size']})");
            }
        }

        if (isset($columns['float'])) {
            foreach ($columns['float'] as $name => $value) {
                $this->addColumn($name, "float({$value['size']},{$value['precision']})");
            }
        }

        if (isset($columns['double'])) {
            foreach ($columns['double'] as $name => $value) {
                $this->addColumn($name, "double({$value['size']},{$value['precision']})");
            }
        }

        if (isset($columns['double_precision'])) {
            foreach ($columns['double_precision'] as $name => $value) {
                $this->addColumn($name, "double precision({$value['size']},{$value['precision']})");
            }
        }

        if (isset($columns['decimal'])) {
            foreach ($columns['decimal'] as $name => $value) {
                $this->addColumn($name, "decimal({$value['size']},{$value['precision']})");
            }
        }

        if (isset($columns['year'])) {
            foreach ($columns['year'] as $name => $value) {
                $this->addColumn($name, 'year');
            }
        }

        if (isset($columns['time'])) {
            foreach ($columns['time'] as $name => $value) {
                $this->addColumn($name, 'time');
            }
        }

        if (isset($columns['date'])) {
            foreach ($columns['date'] as $name => $value) {
                $this->addColumn($name, 'date');
            }
        }

        if (isset($columns['timestamp'])) {
            foreach ($columns['timestamp'] as $name => $value) {
                $this->addColumn($name, 'timestamp');
            }
        }

        if (isset($columns['update_at'])) {
            foreach ($columns['update_at'] as $name => $value) {
                $this->addColumn($name, 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            }
        }

        if (isset($columns['created_at'])) {
            foreach ($columns['created_at'] as $name => $value) {
                $this->addColumn($name, 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
            }
        }

        if (isset($columns['raw'])) {
            foreach ($columns['raw'] as $name => $value) {
                $this->addColumn($name, $value);
            }
        }
    }
}
