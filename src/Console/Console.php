<?php

namespace Haley\Console;

class Console
{
    private static int $group = 0;
    private static array $attributes = [];

    public static function command(string $command, string|array|callable|null $action = null, bool $list = true)
    {
        ConsoleMemory::command($command, $action, $list);

        return new ConsoleOptions;
    }

    public static function namespace(string $value)
    {
        ConsoleMemory::setAttribute('namespace', trim($value, '\\'));
        self::$attributes[self::$group][] = 'namespace';

        return new self;
    }

    public static function prefix(string $value)
    {
        ConsoleMemory::setAttribute('prefix', trim($value));
        self::$attributes[self::$group][] = 'prefix';

        return new self;
    }

    public static function title(string $value)
    {
        ConsoleMemory::setAttribute('title', trim($value, '\\'));
        self::$attributes[self::$group][] = 'title';

        return new self;
    }

    public static function group(callable $routes)
    {
        $group = self::$group;

        self::$group++;

        if (is_callable($routes)) call_user_func($routes, $group);

        foreach (self::$attributes[$group] as $name) {
            ConsoleMemory::removeAttribute($name);
        }

        unset(self::$attributes[$group]);
    }

    public static function end()
    {
        HaleyCommands::run();

        return (new ConsoleResolve)->run(ConsoleMemory::$commands);

        // Console::command('server', false, [Command_Server::class, 'server']);
        // Console::command('server:port', true, [Command_Server::class, 'port']);

        // Console::command('cronjob', false, [Command_Cronjob::class, 'cron']);
        // Console::command('cronjob:run', false, [Command_Cronjob::class, 'run']);
        // Console::command('cronjob:execute', true, [Command_Cronjob::class, 'execute']);

        // Console::command('create:model', true, [Command_Create::class, 'createModel']);
        // Console::command('create:job', true, [Command_Create::class, 'createJob']);
        // Console::command('create:controller', true, [Command_Create::class, 'createController']);
        // Console::command('create:class', true, [Command_Create::class, 'createClass']);
        // Console::command('create:database', true, [Command_Create::class, 'createDatabase']);
        // Console::command('create:middleware', true, [Command_Create::class, 'createMiddleware']);
        // Console::command('create:env', false, [Command_Create::class, 'createEnv']);
        // Console::command('cache:env', false, [Command_Cache::class, 'cache_env']);
        // Console::command('cache:view', false, [Command_Cache::class, 'template_clear']);
        // Console::command('db:migrate', false, [Command_DB::class, 'migrate']);
        // Console::command('db:seeder', false, [Command_DB::class, 'seeder']);
        // Console::command('db:list', false, [Command_DB::class, 'list_migrations']);
        // Console::command('db:drop', true, [Command_DB::class, 'drop']);

        // require_once directoryRoot('routes/console.php');
        // Console::end();
    }
}
