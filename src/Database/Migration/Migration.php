<?php

namespace Haley\Database\Migration;

use Haley\Collections\Config;
use Haley\Collections\Log;
use Haley\Database\Migration\Builder\Builder;
use Haley\Database\Migration\Builder\BuilderMemory;

use InvalidArgumentException;

class Migration
{
    private string|null $connection = null;
    private string|null $driver = null;
    private array|null $config = null;

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
            $this->config = $connections[$config['default']] ?? null;
        } elseif (array_key_exists($connection, $connections)) {
            $this->connection = $connection ?? null;
            $this->driver = $connections[$connection]['driver'] ?? null;
            $this->config = $connections[$connection] ?? null;
        }

        if (empty($this->connection)) {
            Log::create('migration', 'Connection not found');
            throw new InvalidArgumentException('Connection not found');
        }

        if (empty($this->driver) or !in_array($this->driver ?? '', ['mysql', 'pgsql', 'mariadb'])) {
            Log::create('migration', 'Driver not found or not compatible');
            throw new InvalidArgumentException('Driver not found or not compatible');
        }

        return $this;
    }

    public function up(string $table, callable $build)
    {
        BuilderMemory::$connection = $this->connection;
        BuilderMemory::$config = $this->config;
        BuilderMemory::$table = $table;

        $builder = new Builder();

        call_user_func($build, $builder);
    }
}
