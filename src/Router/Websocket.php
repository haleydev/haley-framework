<?php

namespace Haley\Router;

class Websocket
{
    public static function ws(int $port, string|array|callable $action)
    {
        WebsocketMemory::ws($port, $action);

        return new WebsocketOptions;
    }
}
