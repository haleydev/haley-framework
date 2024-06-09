<?php

namespace Haley\Server;

use Haley\Server\WebSocket\WebSocketOptions;

class Server
{
    private static int $group = 0;
    private static array $attributes = [];

    /**
     * Create websocket server
     */
    public static function ws(string $host, int $port, string $class)
    {
        $class = trim($class, '\\');

        ServerMemory::server('websocket', [
            'host' => $host,
            'port' => $port,
            'class' => $class,
            'name' => null,
            'receive' => true,
            'connections' => null,
            'namespace' => null,
            'path' => null
        ]);

        return new WebSocketOptions;
    }

    public static function namespace(string $value)
    {
        ServerMemory::setAttribute('namespace', trim($value, '\\'));
        self::$attributes[self::$group][] = 'namespace';

        return new self;
    }

    public static function name(string $value)
    {
        ServerMemory::setAttribute('name', $value);
        self::$attributes[self::$group][] = 'name';

        return new self;
    }

    public static function group(callable $routes)
    {
        $group = self::$group;

        self::$group++;

        if (is_callable($routes)) call_user_func($routes, $group);

        foreach (self::$attributes[$group] as $name) {
            ServerMemory::removeAttribute($name);
        }

        unset(self::$attributes[$group]);
    }
}
