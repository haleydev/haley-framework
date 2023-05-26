<?php
namespace Core\Database;

use Core\Collections\Config;
use Core\Collections\Log;
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
    public static function instance(string $connection = 'default')
    {
        if (isset(self::$instances[$connection])) {
            return self::$instances[$connection];
        }

        $config = self::getConfig($connection);

        if ($config) {
            $drive = $config['driver'];
            $host = $config['host'];
            $port = $config['port'];
            $dbname = $config['database'];
            $username = $config['username'];
            $password = $config['password'];

            if (isset($config['options']) and !empty($config['options'])) {
                $options = $config['options'];
            } else {
                $options = null;
            }

            self::$instances[$connection] = new PDO("$drive:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
            return self::$instances[$connection];
        }

        Log::create('database', "Connection not found ( {$connection} )");
        throw new PDOException("Connection not found ( {$connection} )");
    }

    /**
     * @return array|false
     */
    public static function getConfig(string $connection = 'default')
    {
        $config = Config::database('connections');

        if ($config and isset($config[$connection])) {
            return $config[$connection];
        }

        return false;
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