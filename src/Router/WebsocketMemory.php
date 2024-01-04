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

    public static function ws(string $url, string|array|callable $action)
    {
        $domain = [];
        $name = null;
        $port = '9215';
        $host = 'localhost';

        self::$sockets[] = [
            'url' => $url,
            'action' => $action,
            'name' => $name,
            'domain' => $domain,
            'port' => $port,
            'host' => $host
        ];
    }
}
