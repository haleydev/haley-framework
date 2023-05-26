<?php
namespace Haley\Console\Commands;
use Haley\Console\Lines;

class Command_Server extends Lines
{   
    public function server()
    {
        echo "\033[0;32mservidor de desenvolvimento ativo:\033[0m http://127.0.0.1:3000" . PHP_EOL;
        shell_exec('php -S 127.0.0.1:3000 ' . directoryRoot('/core/Collections/Server.php') );               
    }

    public function port(int $port)
    {
        if (is_numeric($port)) {
            if (strlen($port) == 4) {
                echo "\033[0;32mservidor de desenvolvimento ativo:\033[0m http://127.0.0.1:$port" . PHP_EOL;
                shell_exec('php -S 127.0.0.1:' . $port . ' ' . directoryRoot('/core/Collections/Server.php') );
            } else {
                $this->red('a porta deve conter 4 números');
            }
        } else {
            $this->red('a porta deve conter apenas números');
        }          
    }
}