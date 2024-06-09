<?php

namespace Haley\Server;

class ServerMemory
{
    public static array $servers = [
        'websocket' => []
    ];

    protected static array $attributes = [
        'namespace' => [],
        'name' => [],
        'host' => [],
        'alias' => []
    ];

    public static function server(string $type, array $params)
    {
        if (count(self::$attributes['namespace'])) $params['namespace'] = implode('\\', self::$attributes['namespace']);
        if (count(self::$attributes['name'])) $params['name'] = implode('.', self::$attributes['name']);
        if (count(self::$attributes['host'])) $params['host'] = implode('.', self::$attributes['host']);
        if (count(self::$attributes['alias'])) $params['alias'] = implode('.', self::$attributes['alias']);

        self::$servers[$type][] = $params;
    }

    public static function setAttribute(string $name, mixed $value)
    {
        self::$attributes[$name][] = $value;
    }

    public static function removeAttribute(string $name)
    {
        if (!count(self::$attributes[$name])) return;

        $key = array_key_last(self::$attributes[$name]);

        if ($key !== null) unset(self::$attributes[$name][$key]);
    }
}
