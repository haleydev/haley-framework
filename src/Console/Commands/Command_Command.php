<?php
namespace Haley\Console\Commands;
use Haley\Console\Lines;

class Command_Command extends Lines
{
    public function command(string $command)
    {
        // $file = ROOT . '/app/Commands/Command_' . $command . '.php';
        // if (file_exists($file)) {
        //     $this->red("substituir command $command ? (s/n) ",false);
        //     $console_env = $this->readline();
        //     if ($console_env == 's') {
        //         $this->new_command($file,$command);
        //     } else {
        //         $this->red('operação cancelada');
        //     }
        // } else {
        //     $this->new_command($file,$command);
        // }
    }

    private function new_command(string $file,string $command)
    {
        // $class = 'Command_' . $command;
        // $mold = mold_command($class);
        // file_put_contents($file, $mold);
        // if (file_exists($file)) {
        //     $this->green("command $command criado com sucesso");
        // } else {
        //     $this->red("falha ao criar command $command");
        // }
    }
}