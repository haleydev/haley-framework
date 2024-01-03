<?php

namespace Haley\Router;

class Websocket
{
    public static function ws(string $url, string|array|callable $action)
    {
        WebsocketMemory::ws($url, $action);

        return new WebsocketOptions; 
    }
}
