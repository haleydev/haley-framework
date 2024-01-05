<?php

namespace Haley\Router;

class WebsocketMemory
{
    public static array $sockets = [];

    private static array $attributes = [
        'name' => [],
        // 'middleware' => [],
        // 'prefix' => [],
        // 'domain' => [],
        // 'namespace' => [],
        // 'error' => []
    ];

    public static function ws(string $port, string|array|callable $action)
    {     
        $name = null;    
        $host = 'localhost';

        self::$sockets[] = [
            // 'url' => $url,
            'action' => $action,
            'name' => $name,         
            'port' => $port,
            'host' => $host,
            'usleep' => 100000
        ];
    }
}
