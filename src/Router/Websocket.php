<?php

namespace Haley\Router;

class Websocket
{
    public static function channel(int $port, string|array|callable $action)
    {
        WebsocketMemory::channel($port, $action);

        return new WebsocketOptions;
    }
}
