<?php

namespace Haley;

use Haley\Collections\Config;
use Haley\Collections\Memory;
use Haley\Console\Console;
use Haley\Exceptions\Exceptions;
use Haley\Router\Route;
use Haley\Router\RouteMemory;

class Kernel
{
    public function run()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_erros', 1);
        error_reporting(E_ALL);

        define('DIRECTORY_PRIVATE', DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'private');
        define('DIRECTORY_PUBLIC', DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'public');
        define('DIRECTORY_RESOURCES', DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'resources');
        define('DIRECTORY_HALEY', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

        date_default_timezone_set(Config::app('timezone'));

        return $this;
    }

    public function app()
    {
        Memory::set('kernel', 'app');

        (new Exceptions)->handler(function () {
            ini_set('session.gc_maxlifetime', Config::app('session')['lifetime']);
            ini_set('session.cookie_lifetime', Config::app('session')['lifetime']);
            ini_set('session.cache_expire', Config::app('session')['lifetime']);
            ini_set('session.name', Config::app('session')['name']);

            if (!empty(Config::app('session')['files'])) {
                createDir(Config::app('session')['files']);
                session_save_path(Config::app('session')['files']);
            }

            if (!isset($_SESSION)) session_start();
            if (Config::app('session')['regenerate']) session_regenerate_id(true);

            ob_start();

            foreach (Config::app('helpers') as $helper) {
                require_once $helper;
            }

            if (!request()->session()->isset('FRAMEWORK')) request()->session()->create('FRAMEWORK');

            $routes = Config::routes();

            if ($routes) {
                foreach ($routes as $name => $config) {
                    $config['name'] = $name;
                    RouteMemory::resetAttributes();

                    RouteMemory::$config = $config;
                    require_once $config['path'];
                }
            }

            Route::end();
        });
    }

    public function console()
    {
        Memory::set('kernel', 'console');

        (new Exceptions)->handler(function () {
            foreach (Config::app('helpers') as $helper) {
                require_once $helper;
            }

            (new Console)->run();
        });
    }

    public function terminate()
    {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        exit;
    }
}
