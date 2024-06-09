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
            ini_set('session.gc_maxlifetime', Config::app('session.lifetime', 86400));
            ini_set('session.cookie_lifetime', Config::app('session.lifetime', 86400));
            ini_set('session.cookie_secure', Config::app('session.secure', true));
            ini_set('session.cache_expire', Config::app('session.lifetime', 86400));
            ini_set('session.name', Config::app('session.name', 'HALEY'));

            if (!empty(Config::app('session.files'))) {
                createDir(Config::app('session.files'));
                session_save_path(Config::app('session.files'));
            }

            if (!isset($_SESSION)) session_start();
            if (Config::app('session.regenerate', false)) session_regenerate_id(true);

            ob_start();

            foreach (Config::app('helpers', []) as $helper) require_once $helper;

            if (!request()->session()->has('HALEY')) request()->session()->set('HALEY');

            $routes = Config::route('http', []);

            if ($routes) {
                foreach ($routes as $name => $config) {
                    if (!file_exists($config['path'])) continue;

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
            foreach (Config::app('helpers', []) as $helper) require_once $helper;
            foreach (Config::route('console', []) as $console) require_once $console;

            Console::end();
        });
    }

    public function onTerminate(string|array|callable $callback)
    {
    }

    public function terminate()
    {
        if (!defined('HALEY_STOP')) define('HALEY_STOP', microtime(true));

        while (ob_get_level() > 0) ob_end_flush();

        // if (!is_null($callback)) executeCallable($callback);

        // echo floor((HALEY_STOP - HALEY_START) * 1000) . 'ms' . '<br>';

        exit;
    }
}
