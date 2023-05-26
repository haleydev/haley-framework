<?php
namespace Haley;

use Haley\Collections\Config;
use Haley\Collections\Memory;
use Haley\Console\Console;
use Haley\Debug\Exceptions;
use Haley\Router\Route;
use Haley\Router\RouteMemory;

class Kernel
{
    public function run()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_erros', 1);
        error_reporting(E_ALL);

        define('DIRECTORY_PRIVATE', ROOT . DIRECTORY_SEPARATOR . 'private');
        define('DIRECTORY_PUBLIC', ROOT . DIRECTORY_SEPARATOR . 'public');
        define('DIRECTORY_RESOURCES', ROOT . DIRECTORY_SEPARATOR . 'resources');

        // require_once ROOT . '/core/Collections/Helpers.php';   

        date_default_timezone_set(Config::app('timezone'));       

        return $this;
    }

    public function app()
    {
        Memory::set('kernel','app');

        (new Exceptions)->handler(function () {
            ini_set('session.gc_maxlifetime', Config::app('session')['lifetime']);
            ini_set('session.cookie_lifetime', Config::app('session')['lifetime']);
            ini_set('session.cache_expire', Config::app('session')['lifetime']);
            ini_set('session.name', Config::app('session')['name']);

            if(!empty(Config::app('session')['files'])) {
                createDir(Config::app('session')['files']);
                session_save_path(Config::app('session')['files']);
            }           

            if (!isset($_SESSION)) session_start();    
            if(Config::app('session')['regenerate']) session_regenerate_id(true); 

            ob_start();
          
            foreach(Config::app('helpers') as $helper) {     
                require_once $helper;
            }

            if (!request()->session()->isset('FRAMEWORK')) request()->session()->create('FRAMEWORK');            

            foreach(Config::routes() as $name => $config) {    
                $config['name'] = $name;    
                RouteMemory::resetAttributes();                      

                RouteMemory::$config = $config;
                require_once $config['path'];              
            }    

            Route::end();
        });
    }

    public function console()
    {
        Memory::set('kernel','console');

        (new Exceptions)->handler(function () { 
            foreach(Config::app('helpers') as $helper) {
                require_once $helper;
            }

            (new Console)->run();
        });
    }

    public function jobs()
    {
        Memory::set('kernel','jobs');

        foreach(Config::app('helpers') as $helper) {
            require_once $helper;
        }
    }

    public function terminate()
    {    
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        exit;
    }
}
