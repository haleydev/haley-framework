<?php

namespace Haley\Console\Commands;


use Haley\Console\Lines;
use Haley\Server\ServerMemory;

class CommandServer extends Lines
{
    public function run(string|null $name = null)
    {

        require_once directoryRoot('routes/server.php');


        dd(ServerMemory::$servers);


        // Shell::kill(Shell::pids());






        // // test
        // $command = sprintf('php %s >/dev/null 2>&1 &', directoryRoot('test.php'));

        // Shell::exec($command, function ($line) {
        //     echo $line . PHP_EOL;
        // });
        // .......

        // $pids = Shell::pids();



        // $command = sprintf('%s >/dev/null 2>&1 &', 'php -S localhost:' . 9011 . ' ' . directoryHaley('Collections/Server.php'));
        // // // // // $command = 'php -S localhost:' . 9004 . ' ' . directoryHaley('Collections/Server.php');

        // Shell::exec($command, function (string $line, array $status) {
        //     // var_dump($line, $status);
        // });


        // require_once directoryRoot('routes/websocket.php');

        // // dd(WebsocketMemory::$sockets);

        // foreach (WebsocketMemory::$sockets as $definitions) {
        //     $this->red($definitions['name'])->br()->br();

        //     (new SocketServer)->run($definitions);
        // }


        // $server = IoServer::factory(
        //     new Teste(),
        //     8080
        // );

        // $server->run();

        // $this->blue('helo ' . $name);
    }
}
