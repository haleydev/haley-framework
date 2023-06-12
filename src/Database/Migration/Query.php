<?php

namespace Haley\Database\Migration;

use Haley\Collections\Config;
use Haley\Collections\Log;
use Haley\Database\Migration\Query\Column;
use Haley\Database\Migration\Query\Constraint;
use Haley\Database\Migration\Query\Table;
use InvalidArgumentException;

class Query
{
    private string|null $connection = null;
    private string|null $driver = null;
    private string|null $database = null;

    public function __construct(string|null $connection = null)
    {
        $this->connection($connection);

        return $this;
    }

    public function connection(string|null $connection = null)
    {
        $config = Config::database();
        $connections = $config['connections'] ?? [];

        if ($connection === null) {
            $this->connection = $config['default'] ?? null;
            $this->driver = $connections[$config['default']]['driver'] ?? null;
            $this->database = $connections[$config['default']]['database'] ?? null;
        } elseif (array_key_exists($connection, $connections)) {
            $this->connection = $connection ?? null;
            $this->driver = $connections[$connection]['driver'] ?? null;
            $this->database = $connections[$connection]['database'] ?? null;
        }

        if (empty($this->connection)) {
            Log::create('migration', "Connection not found");
            throw new InvalidArgumentException("Connection not found");
        }

        if (empty($this->driver) or !in_array($this->driver ?? '', ['mysql', 'pgsql', 'mariadb'])) {
            Log::create('migration', 'Driver not found or not compatible');
            throw new InvalidArgumentException('Driver not found or not compatible');
        }

        return $this;
    }

    public function column()
    {
        return new Column($this->connection, $this->driver, $this->database);
    }

    public function table()
    {
        return new Table($this->connection, $this->driver, $this->database);
    }

    public function constraint()
    {
        return new Constraint($this->connection, $this->driver, $this->database);
    }
}
