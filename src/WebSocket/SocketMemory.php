<?php

namespace Haley\WebSocket;

class SocketMemory
{
    public static int|null $client_id = null;
    public static array $clients = [];
    public static array $props = [];
    public static array $ips = [];
    public static array $headers = [];

    // actions
    public static array $send = [];
    public static array $close = [];
}
