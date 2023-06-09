<?php

namespace Haley\Database\Query\Grammars;

use Haley\Collections\Config;
use Haley\Collections\Log;
use Haley\Database\Query\DB;
use InvalidArgumentException;
use PDO;

class GrammarsHelpers
{
    private string|null $connection = null;
    private array|null $config = null;

    private array $compatibilities = [
        'mysql',
        'pgsql',
        'mariadb'
    ];

    /**
     * Set connection
     */
    public function __construct(string|null $connection = null)
    {
        $config = Config::database();
        $connections = $config['connections'] ?? [];

        if ($connection === null) {
            $this->config = $connections[$config['default']] ?? null;
        } elseif (array_key_exists($connection, $connections)) {
            $this->config = $connections[$connection];
        } else if ($this->config == null) {
            Log::create('connection', "Connection not found");
            throw new InvalidArgumentException("Connection not found");
        }

        if (!in_array($this->config['driver'] ?? '', $this->compatibilities)) {
            Log::create('connection', 'Drive not found ( ' . $this->config['driver'] ?? '' . ' )');
            throw new InvalidArgumentException('Drive not found ( ' . $this->config['driver'] ?? '' . ' )');
        }

        return $this;
    }

    /**
     * Get all drivers
     * @return array
     */
    public function getDrivers()
    {
        return [
            'php' => pdo_drivers(),
            'compatibilities' => $this->compatibilities
        ];
    }

    /**
     * Get atual connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get all tables from the database
     * @return array|null
     */
    public function getTables()
    {
        $data = [];

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?', [$this->config['database']], $this->connection)->fetchAll(PDO::FETCH_OBJ);
            if (count($query)) foreach ($query as $value) $data[] = $value->TABLE_NAME;
        }

        if (count($data)) return $data;

        return null;
    }

    /**
     * Get table info
     * @return array|null
     */
    public function getTableSchema(string $table)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->config['database'], $table], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return $query;
        }

        return null;
    }

    /**
     * Check if table exists
     */
    public function hasTable(string $table)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$this->config['database'], $table], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return true;
        }

        return false;
    }

    public function createTable(string $table, array $columns, string $definitions = '')
    {
        if ($this->hasTable($table)) return false;

        $columns = implode(',', $columns);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("CREATE TABLE `{$table}` ({$columns}) {$definitions}", connection: $this->connection);
        }

        if ($this->hasTable($table)) return true;
    }

    /**
     * Drop table
     */
    public function dropTable(string $table)
    {
        if (!$this->hasTable($table)) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("DROP TABLE `{$table}`", connection: $this->connection);
        }

        return $this->hasTable($table);
    }

    /**
     * Rename table
     */
    public function renameTable(string $old, string $new)
    {
        if (!$this->hasTable($old) or $this->hasTable($new)) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$old}` RENAME TO `{$new}`");
        }

        return $this->hasTable($new);
    }

    /**
     * Get all columns from the table
     * @return array|null
     */
    public function getColumns(string $table)
    {
        $data = [];

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $this->config['database']], $this->connection)->fetchAll(PDO::FETCH_OBJ);
            if (count($query)) foreach ($query as $value) $data[] = $value->COLUMN_NAME;
        }

        if (count($data)) return $data;

        return null;
    }

    /**
     * Get column info
     * @return array|null
     */
    public function getColumnSchema(string $table, string $column)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND COLUMN_NAME = ?', [$table, $this->config['database'], $column], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return $query;
        }

        return null;
    }

    /**
     * Check if column exists
     */
    public function hasColumn(string $table, string $column)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND COLUMN_NAME = ?', [$table, $this->config['database'], $column], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return true;
        }

        return false;
    }

    /**
     * Drop column
     */
    public function dropColumn(string $table, string $column)
    {
        if (!$this->hasColumn($table, $column)) return false;

        $primary = $this->getPrimaryKey($table);
        $foreign = $this->getForeignByColumn($table, $column);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            if ($primary == $column) $this->dropPrimaryKey($table);
            if (!empty($foreign)) $this->dropForeign($table, $foreign['CONSTRAINT_NAME']);

            DB::query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`", connection: $this->connection);
        }

        if (!$this->hasColumn($table, $column)) return true;
    }

    /**
     * Create column
     */
    public function createColumn(string $table, string $column, string $type)
    {
        if ($this->hasColumn($table, $column)) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}");
        }

        if ($this->hasColumn($table, $column)) return true;
    }

    /**
     * Change - update column
     * @return array|false
     */
    public function changeColumn(string $table, string $old_column, string $new_column, string $type)
    {
        if (!$this->hasColumn($table, $old_column) || $this->hasColumn($table, $new_column)) return false;

        $old = $this->getColumnSchema($table, $old_column);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` CHANGE `{$old_column}` `{$new_column}` {$type}");
        }

        $new = $this->getColumnSchema($table, $new_column);

        if (empty($new)) return false;

        $difference = array_diff($new, $old);

        if (!count($difference)) return false;

        return $difference;
    }

    /**
     * Get PrimaryKey from the table
     * @return string|null
     */
    public function getPrimaryKey(string $table)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND COLUMN_KEY = ?', [$table, $this->config['database'], 'PRI'], $this->connection)->fetch(PDO::FETCH_OBJ);
            if (!empty($query)) return $query->COLUMN_NAME;
        }

        return null;
    }

    /**
     * Drop primary key
     */
    public function dropPrimaryKey(string $table)
    {
        $primary = $this->getPrimaryKey($table);

        if (!$primary) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` MODIFY `{$primary}` INT NOT NULL; ALTER TABLE `{$table}` DROP PRIMARY KEY", connection: $this->connection);
        }

        if (!$this->getPrimaryKey($table)) return true;

        return false;
    }

    /**
     * Set primary key
     */
    public function setPrimaryKey(string $table, string $column)
    {
        $this->dropPrimaryKey($table);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)", connection: $this->connection);
        }

        $primary = $this->getPrimaryKey($table);

        if ($primary == $column) return true;

        return false;
    }

    /**
     * Get foreigns
     * @return array|null
     */
    public function getForeigns(string $table)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_COLUMN_NAME IS NOT NULL', [$this->config['database'], $table], $this->connection)->fetchAll(PDO::FETCH_ASSOC);
            if (count($query)) return $query;
        }

        return null;
    }

    /**
     * Get foreign by column
     * @return array|null
     */
    public function getForeignByColumn(string $table, string $column)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_COLUMN_NAME IS NOT NULL AND COLUMN_NAME = ?', [$this->config['database'], $table, $column], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return $query;
        }

        return null;
    }

    /**
     * Check if constraint foreign exists
     */
    public function hasForeign(string $table, string $constraint)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_COLUMN_NAME IS NOT NULL AND CONSTRAINT_NAME = ?', [$this->config['database'], $table, $constraint], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return true;
        }

        return false;
    }

    /**
     * Drop foreign
     */
    public function dropForeign(string $table, string $constraint)
    {
        if (!$this->hasForeign($table, $constraint)) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query('SET FOREIGN_KEY_CHECKS=0', connection: $this->connection);
            DB::query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`", connection: $this->connection);
            DB::query('SET FOREIGN_KEY_CHECKS=1', connection: $this->connection);
        }

        return $this->hasForeign($table, $constraint);
    }

    /**
     * Set foreign
     */
    public function setForeign(string $table, string $column, string $constraint, string $references_table, string $references_column, string $on_delete = 'CASCADE', string $on_update = 'CASCADE')
    {
        if (!$this->hasTable($references_table)) {
            Log::create('database', 'Table references not found ( ' . $references_table . ' )');
            throw new InvalidArgumentException('Table references not found ( ' . $references_table . ' )');
        }

        if (!$this->hasColumn($references_table, $references_column)) {
            Log::create('database', 'Column references not found ( ' . $references_column . ' )');
            throw new InvalidArgumentException('Column references not found ( ' . $references_column . ' )');
        }

        $this->dropForeign($table, $constraint);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`) REFERENCES `{$references_table}`(`{$references_column}`) ON DELETE {$on_delete} ON UPDATE {$on_update}");
        }

        return $this->hasForeign($table, $constraint);
    }

    /**
     * Get indexes
     * @return array|null
     */
    public function getIndexes(string $table)
    {
        $data = [];

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME IS NOT NULL', [$this->config['database'], $table], $this->connection)->fetchAll(PDO::FETCH_OBJ);
            if (count($query)) foreach ($query as $value) $data[] = $value->INDEX_NAME;
        }

        if (count($data)) return $data;

        return null;
    }


    /**
     * Get index by column
     * @return string|null
     */
    public function getIndexByColumn(string $table, string $column)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME IS NOT NULL AND COLUMN_NAME = ?', [$this->config['database'], $table, $column], $this->connection)->fetch(PDO::FETCH_OBJ);
            if (!empty($query)) return $query->INDEX_NAME;
        }

        return null;
    }

    /**
     * Check if index exists
     */
    public function hasIndex(string $table, string $index)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?', [$this->config['database'], $table, $index], $this->connection)->fetch(PDO::FETCH_OBJ);
            if (!empty($query)) return true;
        }

        return false;
    }

    /**
     * Drop index
     */
    public function dropIndex(string $table, string $index)
    {
        if (!$this->hasIndex($table, $index)) return false;

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }

        return $this->hasIndex($table, $index);
    }

    /**
     * Set index
     */
    public function setIndex(string $table, string $column, string $index, string $using = 'BTREE')
    {
        $old = $this->getIndexByColumn($table, $column);

        if ($old) $this->dropIndex($table, $index);

        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$column}`) USING {$using}", connection: $this->connection);
        }

        return $this->getIndexByColumn($table, $column) == $index;
    }
}
