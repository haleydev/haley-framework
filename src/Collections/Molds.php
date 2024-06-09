<?php

namespace Haley\Collections;

class Molds
{
    /**
     * @return string
     */
    public static function middleware(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Middlewares' . $namespace . ';
use Haley\Router\Middleware;

class ' . $class . ' extends Middleware
{
    public function example()
    {
        if(request()->session()->has(\'example\')) {
            return $this->continue();
        }

        return $this->abort(403);
    }
}';
    }

    public static function env()
    {
        return
            'APP_NAME = Code Haley
APP_DEBUG = true
APP_TIMEZONE = America/Sao_Paulo

DB_CONNECTION = mysql
DB_HOST = localhost
DB_PORT = 3306
DB_DATABASE = framework
DB_USERNAME = haley
DB_PASSWORD = root

MAILER_NAME = example
MAILER_RESPONSE = example@hotmal.com
MAILER_HOST =
MAILER_PORT =
MAILER_USERNAME =
MAILER_PASSWORD =

SERVER_HOST = localhost
SERVER_ALIAS = localhost';
    }

    public static function controller(string $class, string $namespace)
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

    public static function class(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Classes' . $namespace . ';

class ' . $class . '
{
    // ...
}';
    }

    public static function database(string $class, string $namespace, string $table)
    {
        return
            '<?php
namespace Database' . $namespace . ';
use Haley\Database\Migrations\Builder\Seeder;
use Haley\Database\Migrations\Builder\Table;

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

    public static function model(string $class, string $table, string|null $primary, array $columns, string $namespace)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = "'$value'";
        }

        !empty($primary) ? $primary = "'$primary'" : $primary = (string)'null';

        return
            '<?php
namespace App\Models' . $namespace . ';
use Haley\Collections\Model;

class ' . $class . ' extends Model
{
    public static string $connection = \'' . Config::database('default', 'mysql') . '\';
    public static string $table = \'' . $table . '\';
    public static string|null $primary = ' . $primary . ';
    public static array $columns = [' . implode(',', $columns) . '];
}';
    }

    public static function job(string $class, string $namespace)
    {
        return
            '<?php
namespace App\Jobs' . $namespace . ';

/**
 * CAUTION: If the script is too long, it is recommended that you create another job to run it asynchronously.
 */
class ' . $class . '
{
    public function job()
    {
      // ...
    }
}';
    }
}
