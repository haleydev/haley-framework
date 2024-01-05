<?php

namespace Haley\WebSocket;

class SocketMemory
{
    public static int|null $id = null;
    public static array $send = [];
    public static array $clients = [];
    public static array $props = [];
    public static array $ip = [];

    public static function reset()
    {
        self::$send = [];
    }
}
