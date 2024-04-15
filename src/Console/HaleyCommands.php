<?php

namespace Haley\Console;

class HaleyCommands
{
    public static function run()
    {
        Console::namespace('Haley\Console\Commands')->group(function () {
            Console::command('', 'CommandDashboard::run', false);
            Console::command('serve {port?}', 'CommandServe::run')->description('development Server');         

            Console::title('Create')->prefix('create:')->group(function () {
                Console::command('env', 'CommandCreate::env')->description('create a env file');
                Console::command('class {name}', 'CommandCreate::class')->description('create a new class');
                Console::command('controller {name}', 'CommandCreate::controller')->description('create a new controller class');
                Console::command('job {name}', 'CommandCreate::job')->description('create a new job class');
                Console::command('migration {name}', 'CommandCreate::database')->description('create a new migration class');
                Console::command('middleware {name}', 'CommandCreate::middleware')->description('create a new middleware class');
                Console::command('model {name} {connection?}', 'CommandCreate::model')->description('create a new model class / name --all to create all models in the database');
            });
            
            Console::title('Clean')->group(function () {
            });

            Console::title('Job')->prefix('job:')->group(function () {
                Console::command('active', 'CommandJobs::active')->description('enable or disable jobs \'linux debian\' - ' . self::checkJob());
                Console::command('run {name?}', 'CommandJobs::run')->description('execute jobs');
                Console::command('execute {key}', 'CommandJobs::execute', false);
            });

            Console::title('Migration')->prefix('migration:')->group(function () {
                Console::command('run {name?}', 'CommandMigration::run')->description('run pending migrations');
            });    

            Console::title('Websocket')->prefix('websocket:')->group(function () {
                Console::command('run {name?}', 'CommandWebsocket::run')->description('run websocket');
            });    
            
            
        });
    }

    private static function checkJob()
    {
        if (strtolower(PHP_OS) == 'linux') {
            $service = shell_exec('service cron status 2>&1');

            if (str_contains($service, 'running') or str_contains($service, 'active')) {

                $check = shell_exec('crontab -l 2>&1');

                if (str_contains($check, '* * * * * cd ' . directoryRoot() . ' && php haley job:run >> /dev/null 2>&1')) {
                    return "\033[0;32menabled\033[0m";
                }
            }
        }

        return "\033[0;31mdisabled\033[0m";
    }
}
