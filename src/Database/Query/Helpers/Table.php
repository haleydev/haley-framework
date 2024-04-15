<?php

namespace Haley\Database\Query\Helpers;

use Haley\Collections\Log;
use Haley\Database\DB;
use InvalidArgumentException;
use PDO;

class Table
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
     * @return bool
     */
    public function has(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND TABLE_TYPE = 'BASE TABLE'", [$this->database, $table], $this->connection)->fetch(PDO::FETCH_ASSOC);
            if (!empty($query)) return true;
        } else {
            $this->driverError($this->driver);
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getSchema(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND TABLE_TYPE = 'BASE TABLE'", [$this->database, $table], $this->connection)->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($query)) return $query;
        } else {
            $this->driverError($this->driver);
        }

        return null;
    }

    // return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';

    /**
     * @return array|null
     */
    public function getNames()
    {
        $names = [];

        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $query = DB::query("SELECT TABLE_NAME AS `table_name` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'", [$this->database], $this->connection)->fetchAll(PDO::FETCH_ASSOC);
            if (count($query)) foreach ($query as $value) $names[] = $value['table_name'];
        } else {
            $this->driverError($this->driver);
        }

        if (count($names)) return $names;

        return null;
    }

    /**
     * @return bool
     */
    public function drop(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query('DROP TABLE ' . $table, connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return !$this->has($table);
    }

    /**
     * @return bool
     */
    public function dropIfExists(string $table)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            DB::query('DROP TABLE IF EXISTS ' . $table, connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return !$this->has($table);
    }

    /**
     * @return bool
     */
    public function create(string $table, array $columns, string|null $definitions = null)
    {
        if (in_array($this->driver, ['mysql', 'pgsql', 'mariadb'])) {
            $columns = implode(',', $columns);
            $definitions = $definitions ?? '';

            DB::query(trim("CREATE TABLE `{$table}` ({$columns}) {$definitions}"), connection: $this->connection);
        } else {
            $this->driverError($this->driver);
        }

        return $this->has($table);
    }

    private function driverError(string $driver)
    {
        Log::create('migration', 'Driver not found for ' . $driver);
        throw new InvalidArgumentException('Driver not found for ' . $driver);
    }
}
