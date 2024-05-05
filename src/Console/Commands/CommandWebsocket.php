<?php

namespace Haley\Console\Commands;

use App\Controllers\WebSocket\Teste;
use Haley\Console\Lines;
use Haley\Router\WebsocketMemory;
use Haley\Shell\Shell;
use Haley\WebSocket\SocketServer;
use Ratchet\Server\IoServer;

class CommandWebsocket extends Lines
{
    public function run(string|null $name = null)
    {
        // var_dump(Shell::memory(69266));

        // $test = Shell::readline();

        // Shell::red($test)->br();

        Shell::magenta('aaaaaaa');

        // Shell::list(shell::green('command', true, false) . shell::red('[blabla]', false, false), 'word word word word word word word')->br();
        //  Shell::list(shell::blue('helo', false, false), 'word')->br();
        // Shell::list(shell::yellow('helo', false, false), 'word')->br();
        // Shell::list(shell::red('helo', false, false), 'word')->br();
        // Shell::list(shell::gray('helo', false, false), 'word')->br();

        // // test
        // $command = sprintf('php %s >/dev/null 2>&1 &', directoryRoot('test.php'));

        // Shell::exec($command, function ($line) {
        //     echo $line . PHP_EOL;
        // });
        // .......

        // $pids = Shell::pids();

        //





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
