<?php

namespace Haley\Console\Commands;

use Haley\Console\Lines;
use Haley\Router\WebsocketMemory;
use Haley\WebSocket\SocketServer;

class CommandWebsocket extends Lines
{
    public function run(string|null $name = null)
    {
        require_once directoryRoot('routes/socket.php');

        // dd(WebsocketMemory::$sockets);

        foreach (WebsocketMemory::$sockets as $definitions) {
            // $this->red($definitions['name']);

            SocketServer::run($definitions);
        }

        // $this->blue('helo ' . $name);
    }
}
