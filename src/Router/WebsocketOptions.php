<?php

namespace Haley\Router;

class WebsocketOptions
{
    public function name(string $value)
    {
        $key = array_key_last(WebsocketMemory::$sockets);

        if (empty(WebsocketMemory::$sockets[$key]['name'])) {
            WebsocketMemory::$sockets[$key]['name'] = $value;
        } else {
            WebsocketMemory::$sockets[$key]['name'] .= '.' . $value;
        }

        return $this;
    }

    public function host(string $value)
    {
        $key = array_key_last(WebsocketMemory::$sockets);
        WebsocketMemory::$sockets[$key]['host'] = $value;

        return $this;
    }

    public function port(int $value)
    {
        $key = array_key_last(WebsocketMemory::$sockets);
        WebsocketMemory::$sockets[$key]['port'] = $value;

        return $this;
    }
}
