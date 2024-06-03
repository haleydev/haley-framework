<?php

namespace Haley\Server\WebSocket;

use Haley\Server\ServerMemory;

class WebSocketOptions
{
    public function name(string $value)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['name'] = $value;

        return new self;
    }

    public function receive(bool $value = true)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['receive'] = $value;

        return new self;
    }
}
