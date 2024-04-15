<?php

namespace Haley\Console\Commands;

use App\Controllers\WebSocket\Teste;
use Haley\Console\Lines;
use Haley\Router\WebsocketMemory;
use Haley\WebSocket\SocketServer;
use Ratchet\Server\IoServer;

class CommandWebsocket extends Lines
{
    public function run(string|null $name = null)
    {
        require_once directoryRoot('routes/websocket.php');

        // dd(WebsocketMemory::$sockets);

        foreach (WebsocketMemory::$sockets as $definitions) {
            // $this->red($definitions['name']);

            SocketServer::run($definitions);
        }


        // $server = IoServer::factory(
        //     new Teste(),
        //     8080
        // );

        // $server->run();

        // $this->blue('helo ' . $name);
    }
}
