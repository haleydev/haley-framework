<?php

namespace Haley\Database\Migration\Query;

use Haley\Collections\Log;
use Haley\Database\Query\DB;
use InvalidArgumentException;
use PDO;

class Constraint
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
     * Define primary key of the table
     * @return bool
     */
    public function setPrimaryKey(string $table, string $column)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query(sprintf("ALTER TABLE %s ADD PRIMARY KEY %s(%s)", $table, 'primary_' . $table . '_' . $column, $column), connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return $this->getPrimaryKey($table) == $column;
    }

    /**
     * Get PrimaryKey from the table
     * @return string|null
     */
    public function getPrimaryKey(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND CONSTRAINT_NAME = ?', [$table, $this->database, 'PRIMARY'], $this->connection)->fetch(PDO::FETCH_OBJ);
            if (!empty($query)) return $query->COLUMN_NAME;
        } else {
            $this->driverError($this->driver);
        }

        return null;
    }

    /**
     * Drop PrimaryKey from the table
     * @return bool
     */
    public function dropPrimaryKey(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query(sprintf('ALTER TABLE %s DROP PRIMARY KEY', $table), connection: $this->connection)->fetch(PDO::FETCH_OBJ);
        } else {
            $this->driverError($this->driver);
        }

        return $this->getPrimaryKey($table) === null;
    }

    /**
     * Set column id
     */
    public function setId(string $table,  $column, string|null $comment)
    {
        $atual = $this->getPrimaryKey($table);
        $has_column = DB::helper($this->connection)->column()->has($table, $column);
        $unset_atual = false;

        if ($atual !== null and $atual !== $column) $unset_atual = true;

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            if ($unset_atual) {
                DB::helper($this->connection)->column()->change($table, $atual, 'INT NOT NULL');
                $this->dropPrimaryKey($table);
            }

            $comment = $comment ? " COMMENT '$comment'" : '';

            if ($has_column) {
                if ($atual !== $column) $this->setPrimaryKey($table, $column);
                DB::helper($this->connection)->column()->change($table, $column, 'INT NOT NULL AUTO_INCREMENT' . $comment);
            } else {
                DB::helper($this->connection)->column()->create($table, $column, 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' . $comment);
            }
        } else {
            $this->driverError($this->driver);
        }

        return $this->getPrimaryKey($table) == $column;
    }

    private function driverError(string $driver)
    {
        Log::create('migration', 'Driver not found for ' . $driver);
        throw new InvalidArgumentException('Driver not found for ' . $driver);
    }
}
