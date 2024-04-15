<?php

namespace Haley\Database;

use Haley\Collections\Config;
use Haley\Collections\Log;
use PDO;
use PDOException;

/**
 * Gerencia as conexões com o banco de dados
 */
class Connection
{
    private static array $instances;

    /**
     * Criar conexão com o banco de dados
     * @return PDO
     */
    public static function instance(string $connection)
    {
        if (isset(self::$instances[$connection])) return self::$instances[$connection];        

        $config = Config::database('connections');

        if (!empty($config[$connection])) {
            $config = $config[$connection];

            $drive = $config['driver'];
            $host = $config['host'];
            $port = $config['port'];
            $dbname = $config['database'];
            $username = $config['username'];
            $password = $config['password'];
            $options = null;

            if (isset($config['options']) and !empty($config['options'])) $options = $config['options'];           

            self::$instances[$connection] = new PDO("$drive:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
            return self::$instances[$connection];
        }

        Log::create('database', "Connection not found ( {$connection} )");
        throw new PDOException("Connection not found ( {$connection} )");
    }

    public static function close(string $connection = null)
    {
        if (!empty($connection)) {
            if (isset(self::$instances[$connection])) {
                self::$instances[$connection] = null;
                unset(self::$instances[$connection]);
            }

            return;
        }

        foreach (self::$instances as $key => $instance) {
            self::$instances[$key] = null;
            unset(self::$instances[$key]);
        }

        self::$instances = [];
    }

    public function __destruct()
    {
        self::close();
    }
}
