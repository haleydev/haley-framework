<?php

namespace Haley\WebSocket;

class SocketMemory
{
    public static int|null $id = null;   
    public static array $clients = [];
    public static array $props = [];
    public static array $ips = [];

    // actions
    public static array $send = [];
    public static array $close = [];

    public static function reset()
    {
        self::$send = [];
        // self::$close = [];
    }
}
