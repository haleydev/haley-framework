<?php

namespace Haley\Console\Commands;

use Haley\Console\ConsoleMemory;
use Haley\Console\Lines;
use Haley\Collections\Str;

class CommandDashboard extends Lines
{
    public function run()
    {
        $commands = ConsoleMemory::$commands;
        $commands_list = [];
        $space = 0;

        foreach ($commands as $value) {
            if ($value['list']) {
                $title = $value['title'] ?? 'Available commands';

                if (!array_key_exists($title, $commands_list)) $commands_list[$title] = [];

                $command = $value['command'];
                $options = [];

                if (preg_match_all('/{(.*?)}/', $command, $matches)) {
                    foreach ($matches[0] as $k => $v) {
                        $required = Str::end($matches[1][$k] ?? '', '?') ? 'optional' : 'required';
                        $n = trim($matches[1][$k] ?? '', '?');

                        $command = trim(str_replace($v, '', $command));
                        $options[] = "[$required:$n]";
                    }
                };

                $options = implode(' ', $options);

                $strlen = strlen($command . $options);

                if ($strlen > $space) $space = $strlen;

                $commands_list[$title][] = [
                    'command' => $command,
                    'options' => $options,
                    'description' => $value['description'] ?? null
                ];
            }
        }



        Lines::br()->blue('haley v1.0.0 beta - Warley Rodrigues de Moura')->br();

        foreach ($commands_list as $title => $value) {
            Lines::br()->yellow($title)->br();

            foreach ($value as $c) {
                $max = $space - strlen($c['options']) - strlen($c['command']) + 5;

                Lines::green($c['command']);

                if (strlen($c['options'])) {
                    Lines::blue($c['options']);
                } else {
                    $max++;
                }

                if ($c['description']) {
                    while ($max > 0) {
                        echo "\e[90m-\033[0m";
                        $max--;
                    }

                    Lines::normal(' ' . $c['description'])->br();
                }else{
                    Lines::br();
                }
            }
        }

        Lines::br();


        // echo "\033[0;33m database\033[0m" . PHP_EOL;
        // echo "\033[0;32m db:migrate\033[0m executa as bases de dados/models pendentes" . PHP_EOL;
        // echo "\033[0;32m db:seeder\033[0m executa os seeders pendentes" . PHP_EOL;
        // echo "\033[0;32m db:drop nome\033[0m exclui uma tabela do banco de dados" . PHP_EOL;
        // echo "\033[0;32m db:conexao\033[0m testa a conexão com o banco de dados" . PHP_EOL;
        // echo "\033[0;32m db:list\033[0m lista todas as migrações já executadas" . PHP_EOL . PHP_EOL;

        // echo "\033[0;33m cache\033[0m" . PHP_EOL;
        // echo "\033[0;32m cache:view\033[0m limpa o cache dos view" . PHP_EOL;     
        // echo "\033[0;32m cache:env\033[0m armazena e usa as informações do .env em cache - " . $this->env_check() . PHP_EOL . PHP_EOL;  
    }

    private function env_check()
    {
        if (file_exists(directoryRoot('storage/cache/jsons/env.json'))) {
            return "\033[0;32mativo\033[0m" . PHP_EOL;
        } else {
            return "\033[0;31mdesativado\033[0m" . PHP_EOL;;
        }
    }

    private function cron_check()
    {
        if (strtolower(PHP_OS) == 'linux') {
            $service = shell_exec('service cron status 2>&1');

            if (str_contains($service, 'cron is running') or str_contains($service, 'active (running)')) {

                $check = shell_exec('crontab -l 2>&1');

                if (str_contains($check, '* * * * * cd ' . directoryRoot() . ' && php haley job:run >> /dev/null 2>&1')) {
                    return "\033[0;32mativo\033[0m" . PHP_EOL;
                }
            }
        }

        return "\033[0;31mdesativado\033[0m" . PHP_EOL;
    }
}
