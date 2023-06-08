<?php

namespace Haley\Console;

use Haley\Console\Commands\{Command_Dashboard};

use Haley\Console\Commands\Command_Cache;
// use Haley\Console\Commands\Command_Command;
use Haley\Console\Commands\Command_Create;
use Haley\Console\Commands\Command_Cronjob;
use Haley\Console\Commands\Command_DB;
use Haley\Console\Commands\Command_Server;

class Console
{
    private static $commands = [];

    public static function command(string $command, bool $headline, array|callable $action)
    {
        self::$commands[$command] = [
            'action' => $action,
            'headline' => $headline
        ];
    }

    private static function invalid(string|null $mesage = null)
    {
        if ($mesage == null) {
            return die("\033[0;31mcomando inválido\033[0m" . PHP_EOL);
        }

        return die("\033[0;31m$mesage\033[0m" . PHP_EOL);
    }

    private static function head()
    {
        global $argv;

        array_shift($argv);

        isset($argv[0]) ? $command = trim($argv[0]) : $command = null;

        isset($argv[1]) ? $headline = trim($argv[1]) : $headline = null;

        return [
            'command' => $command,
            'headline' => $headline
        ];
    }

    private static function action($action, $headline)
    {
        if (is_array($action)) {
            $action[0] = new $action[0]();
        }

        if (is_callable($action)) {
            if ($headline == null) {
                return call_user_func($action);
            } else {
                return call_user_func($action, $headline);
            }
        }

        self::invalid('Erro ao executar comando');
    }

    public static function end()
    {
        $head = self::head();

        if ($head['command'] == '' and $head['headline'] == null) {
            return (new Command_Dashboard)->dashboard();
        }

        if (count(self::$commands) == 0) {
            return self::invalid();
        }

        foreach (self::$commands as $command => $value) {
            if ($command == $head['command']) {
                if ($value['headline'] == true and $head['headline'] != null) {
                    return self::action($value['action'], $head['headline']);
                }

                if ($value['headline'] == false and $head['headline'] == null) {
                    return self::action($value['action'], false);
                }
            }
        }

        return self::invalid();
    }

    public function run()
    {
        Console::command('server', false, [Command_Server::class, 'server']);
        Console::command('server:port', true, [Command_Server::class, 'port']);

        Console::command('cronjob', false, [Command_Cronjob::class, 'cron']);
        Console::command('cronjob:run', false, [Command_Cronjob::class, 'run']);
        Console::command('cronjob:execute', true, [Command_Cronjob::class, 'execute']);

        Console::command('create:model', true, [Command_Create::class, 'createModel']);
        Console::command('create:job', true, [Command_Create::class, 'createJob']);
        Console::command('create:controller', true, [Command_Create::class, 'createController']);
        Console::command('create:class', true, [Command_Create::class, 'createClass']);
        Console::command('create:database', true, [Command_Create::class, 'createDatabase']);
        Console::command('create:middleware', true, [Command_Create::class, 'createMiddleware']);
        Console::command('create:env', false, [Command_Create::class, 'createEnv']);
        Console::command('cache:env', false, [Command_Cache::class, 'cache_env']);
        Console::command('cache:view', false, [Command_Cache::class, 'template_clear']);
        Console::command('db:migrate', false, [Command_DB::class, 'migrate']);
        Console::command('db:seeder', false, [Command_DB::class, 'seeder']);
        Console::command('db:list', false, [Command_DB::class, 'list_migrations']);
        Console::command('db:drop', true, [Command_DB::class, 'drop']);

        // require_once directoryRoot('routes/console.php');
        Console::end();
    }
}
