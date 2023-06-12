<?php

namespace Haley\Database\Migration\Query;

use Haley\Collections\Log;
use InvalidArgumentException;

class Foreign
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

    private function driverError(string $driver)
    {
        Log::create('migration', 'Driver not found for ' . $driver);
        throw new InvalidArgumentException('Driver not found for ' . $driver);
    }
}
