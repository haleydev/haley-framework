<?php

namespace Haley\Server\WebSocket;

use Haley\Server\ServerMemory;

class WebSocketOptions
{
    /**
     * Allow customers to send
     */
    public function receive(bool $value = true)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['receive'] = $value;

        return $this;
    }

    /**
     * Set number of connections
     */
    public function connections(int|null $value = null)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['connections'] = $value;

        return $this;
    }

    /**
     * Set server name
     */
    public function name(string $value)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        if (empty(ServerMemory::$servers['websocket'][$key]['name'])) {
            ServerMemory::$servers['websocket'][$key]['name'] = $value;
        } else {
            ServerMemory::$servers['websocket'][$key]['name'] .= '.' . $value;
        }

        return $this;
    }

    public function path(string $value)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['path'] = $value;

        return $this;
    }

    public function host(string $value)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['host'] = $value;

        return $this;
    }

    public function alias(string $value)
    {
        $key = array_key_last(ServerMemory::$servers['websocket']);

        ServerMemory::$servers['websocket'][$key]['alias'] = $value;

        return $this;
    }
}
