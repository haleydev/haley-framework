<?php

namespace Haley\Server;

use Haley\Server\WebSocket\WebSocketOptions;

class Server
{
    public static function ws(string $host, int $port, string|array|callable $action)
    {
        ServerMemory::server('websocket', [
            'host' => $host,
            'port' => $port,
            'action' => $action,
            'name' => null,
            'receive' => true
        ]);

        return new WebSocketOptions;
    }
}
