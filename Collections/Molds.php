<?php

namespace Core\Collections;

class Molds
{
    /**
     * @return string
     */
    public function middleware(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Middlewares' . $namespace . ';
use Core\Router\Middleware;

class ' . $class . ' extends Middleware
{
    public function example()
    {
        if(request()->session()->isset(\'example\')) {
            return $this->continue();
        }

        return $this->stop(403);
    }
}';
    }

    public function env()
    {
        return
            'APP_NAME = Code Halley
APP_DEBUG = true
APP_TIMEZONE = America/Los_Angeles

DB_DRIVE = mysql
DB_HOST = localhost
DB_PORT = 3306
DB_DATABASE = haley
DB_USERNAME = haley
DB_PASSWORD = root

MAILER_NAME = example
MAILER_RESPONSE = example@hotmal.com
MAILER_HOST =
MAILER_PORT =
MAILER_USERNAME =
MAILER_PASSWORD =';
    }

    public function controller(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Controllers' . $namespace . ';
use App\Controllers\Controller;

class ' . $class . ' extends Controller
{   
    // ...   
}';
    }

    public function class(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Classes' . $namespace . ';

class ' . $class . '
{           
    // ...             
}';
    }

    public function database(string $class, string $namespace, string $table)
    {
        return
            '<?php
namespace Database' . $namespace . ';
use Core\Database\Migrations\Builder\Seeder;
use Core\Database\Migrations\Builder\Table;

/**
 * Created at ' . date('d/m/Y - H:i:s') . '
 */ 
class ' . $class . '
{
    public bool $active = true;

    public function migrate(Table $table)
    {  
        $table->definitions(\'' . $table . '\');       
        $table->primary(\'id\');  
    
        
        $table->updateDate();
        $table->createdDate();
    }

    public function seeder(Seeder $seeder)
    {
        $seeder->definitions(\'' . $table . '\'); 

        $seeder->values([
            [
               \'id\' => 1
            ],

            [
                \'id\' => 2
            ]
        ]);  
    }
}';
    }

    public function model(string $class, string $table, string|null $primary, array $columns ,string $namespace)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = "'$value'";
        }

        !empty($primary) ? $primary = "'$primary'" : $primary = (string)'null';
      
        return
            '<?php        
namespace App\Models' . $namespace . ';
use Core\Collections\Model;       

class ' . $class . ' extends Model
{   
    public static string $table = \'' . $table . '\';
    public static string|null $primary = ' . $primary . ';
    public static array $columns = [' . implode(',', $columns) . ']; 
}';
    }

    public function job(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Jobs' . $namespace . ';
use Core\Cron;

/**
 * CUIDADO: Se o escript for muito demorado e recomendado que se crie outro documento cronjob para que seja executado de forma assíncrona.
 */        
class ' . $class . ' 
{
    public function job(Cron $schedule)
    {
        $schedule->cron(\'03:03\',\'03/03/2023\',function(){

        })->description(\'example\');
    }
}';
    }
}
