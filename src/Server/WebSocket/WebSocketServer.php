<?php

namespace Haley\Server\WebSocket;

use Haley\Server\ServerMemory;

class WebSocketServer
{
    public function run(array $params)
    {
        $server = new Swoole\WebSocket\Server('0.0.0.0', 9502);

        // Listen to the WebSocket connection open event.
        $server->on('Open', function ($server, $request) {
            $server->push($request->fd, "hello, welcome\n");
        });

        // Listen to the WebSocket message event.
        $server->on('Message', function ($server, $frame) {
            echo "Message: {$frame->data}\n";
            $server->push($frame->fd, "server: {$frame->data}");
        });

        // Listen to the WebSocket connection close event.
        $server->on('Close', function ($server, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $server->start();
    }
}
