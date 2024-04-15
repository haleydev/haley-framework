<?php

namespace Haley\Database;

use Haley\Collections\Config;
use Haley\Database\Connection;
use Haley\Database\Migration\Helper;
use Haley\Database\Query\Builder;

class DB
{
    /** 
     * Connection instance
     */
    public static function connection(string|null $connection = null)
    {
        if ($connection === null) $connection = Config::database('default');

        return Connection::instance($connection);
    }

    /** 
     * Raw query
     */
    public static function query(string $query, array $bindparams = [], string|null $connection = null)
    {
        if ($connection === null) $connection = Config::database('default');

        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count, $value);
                $count++;
            }
        }

        $query->execute();

        return $query;
    }

    /**   
     * Query table
     */
    public static function table(string $table, null|string $as = null)
    {
        return (new Builder)->table($table, $as);
    }

    /**
     * Database migration helper
     */
    public static function helper(string|null $connection = null)
    {
        return new Helper($connection);
    }
}
