<?php
namespace Haley\Console\Commands;

use Haley\Collections\Websocket;
use Haley\Console\Lines;
use Haley\Router\WebsocketMemory;

class CommandWebsocket extends Lines
{
    public function run(string|null $name = null)
    {
        require_once directoryRoot('routes/socket.php');

        // dd(WebsocketMemory::$sockets);

        foreach(WebsocketMemory::$sockets as $definitions) {
            // $this->red($definitions['name']);
            
            Websocket::run($definitions);
        }

        // $this->blue('helo ' . $name);
    }
}