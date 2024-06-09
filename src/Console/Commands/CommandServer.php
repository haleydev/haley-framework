<?php

namespace Haley\Console\Commands;


use Haley\Console\Lines;
use Haley\Server\ServerMemory;
use Haley\Server\WebSocket\WebSocketServer;

class CommandServer extends Lines
{
    public function run(string|null $name = null)
    {
        if (!extension_loaded('swoole')) {
            Lines::red('swoole extension not found')->br();

            return;
        }

        require_once directoryRoot('routes/server.php');

        if (count(ServerMemory::$servers['websocket'])) (new WebSocketServer)->run(ServerMemory::$servers['websocket'][0]);

        // dd(ServerMemory::$servers);


        // Shell::kill(Shell::pids());
    }
}
