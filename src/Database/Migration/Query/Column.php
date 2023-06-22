<?php

namespace Haley\Database\Migration\Query;

use Haley\Collections\Log;
use Haley\Database\Query\DB;
use InvalidArgumentException;
use PDO;

class Column
{
    private string $connection;
    private string $driver;
    private string $database;

    public function __construct(string $connection, string $drive, string $database)
    {
        $this->connection = $connection;
        $this->driver = $drive;
        $this->database = $database;
    }

    /**
     * @return array|null
     */
    public function getNames(string $table)
    {
        $data = [];

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT COLUMN_NAME as `column_name` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?', [$table, $this->database], $this->connection)->fetchAll(PDO::FETCH_OBJ);
            if (count($query)) foreach ($query as $value) $data[] = $value->column_name;
        } else {
            $this->driverError($this->driver);
        }

        if (count($data)) return $data;

        return null;
    }

    /**
     * @return bool
     */
    public function has(string $table, string $column)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$this->database, $table, $column], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return true;
        } else {
            $this->driverError($this->driver);
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getSchema(string $table, string $column)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$this->database, $table, $column], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return $query;
        } else {
            $this->driverError($this->driver);
        }

        return null;
    }

    /**
     * Create column
     * @return bool
     */
    public function create(string $table, string $column, string $type)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}", connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return $this->has($table, $column);
    }

    /**
     * Change column - return array diference
     * @return array|false
     */
    public function change(string $table, string $column, string $type, string|null $rename = null)
    {
        if (!$this->has($table, $column));

        if ($rename === null) {
            $rename = $column;
        } elseif ($this->has($table, $rename)) {
            return false;
        }

        $old = $this->getSchema($table, $column);

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query("ALTER TABLE `{$table}` CHANGE `{$column}` `{$rename}` {$type}", connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        $new = $this->getSchema($table, $rename);

        if (empty($new)) return false;

        $difference = array_diff($new, $old);

        if (!count($difference)) return false;

        return $difference;
    }

    /**
     * Rename column
     * @return bool
     */
    public function rename(string $table, string $column, string $to)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query(sprintf('ALTER TABLE %s RENAME COLUMN %s to %s', $table, $column, $to), connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return $this->has($table, $to);
    }

    private function driverError(string $driver)
    {
        Log::create('migration', 'Driver not found for ' . $driver);
        throw new InvalidArgumentException('Driver not found for ' . $driver);
    }
}
