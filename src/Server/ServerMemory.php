<?php

namespace Haley\Server;

class ServerMemory
{
    public static array $servers = [
        'websocket' => []
    ];

    public static function server(string $type, array $params)
    {
        self::$servers[$type][] = $params;
    }
}
