<?php

namespace Haley\Database\Migrations;

use Haley\Database\Migrations\Builder\MigrationMemory;
use Haley\Database\Migrations\Builder\Table;
use Haley\Database\Migrations\Builder\Seeder;
use Haley\Database\Migrations\Syntaxes\Mysql;
use Haley\Database\Query\DB;
use Haley\Collections\Log;
use FilesystemIterator;
use Haley\Collections\Config;
use InvalidArgumentException;
use PDOException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Migration
{
    public function migrate()
    {
        $params = $this->migrationFiles('database');

        if (count($params) > 0) {
            foreach ($params as $param) {
                $table = new Table;
                $migration = $param['class'];
                $migration = new $migration;

                if ($migration->active == true) {
                    $migration->migrate($table);

                    MigrationMemory::$definitions['migration'] = $param['migration'];

                    if (!empty($connection)) {
                        MigrationMemory::$definitions['connection'] = $connection;
                    }

                    $connection = MigrationMemory::$definitions['connection'];
                    if ($connection == null) $connection = Config::database('default');

                    $config = Config::database('connections')[$connection] ?? null;

                    // dd($config);die;

                    if (empty($config)) {
                        Log::create('migration', "Connection not found ( {$connection} )");
                        MigrationMemory::message("connection not found ( {$connection} )", true);
                        return;
                    }

                    $execute = $this->syntaxeMigration($config['driver']);

                    if ($execute) {
                        $execute->migrate();
                    } else {
                        Log::create('migration', "Drive not found! ( {$config['driver']} )");
                        MigrationMemory::message("Drive not found! ( {$config['driver']} )", true);
                    }

                    MigrationMemory::reset();
                }
            }
        }
    }

    public function seeders(string|null $connection = null)
    {
        $params = $this->migrationFiles('database');

        if (count($params) > 0) {
            foreach ($params as $param) {
                $seeder = new Seeder;
                $migration = $param['class'];
                $migration = new $migration;

                if ($migration->active == true) {
                    $migration->seeder($seeder);

                    MigrationMemory::$definitions['migration'] = $param['migration'];

                    if (!empty($connection)) {
                        MigrationMemory::$definitions['connection'] = $connection;
                    }

                    $connection = MigrationMemory::$definitions['connection'];
                    if ($connection == null) $connection = Config::database('default');

                    $config = Config::database('connections')[$connection] ?? null;

                    if (empty($config)) {
                        Log::create('migration', "Connection not found ( {$connection} )");
                        MigrationMemory::message("connection not found ( {$connection} )", true);
                        return;
                    }

                    try {
                        $seeders = MigrationMemory::$seeders;

                        if (!empty($seeders)) {
                            $insert = DB::table(MigrationMemory::$definitions['table'])->connection($connection)->insertIgnore($seeders);

                            if ($insert > 0) {
                                MigrationMemory::message("$insert seeders inserted");
                            }
                        }
                    } catch (PDOException $error) {
                        MigrationMemory::message($error->getMessage(), true);
                        Log::create('migration', $error->getMessage());
                    }

                    MigrationMemory::reset();
                }
            }
        }
    }

    public function dropTable(string $table, string|null $connection = null)
    {
        if ($connection == null) $connection = Config::database('default');

        $config = Config::database('connections')[$connection] ?? null;

        if (empty($config)) {
            Log::create('migration', "Connection not found ( {$connection} )");
            return "connection not found ( {$connection} )";
        }

        $execute = $this->syntaxeMigration($config['driver']);

        if ($execute) {
            return $execute->dropTable($table, $connection);
        } else {
            Log::create('migration', "Drive not found! ( {$config['driver']} )");
            return "Drive not found! ( {$config['driver']} )";
        }
    }

    public function modelInfo(string|null $connection = null)
    {
        if ($connection == null) $connection = Config::database('default');

        $config = Config::database('connections')[$connection] ?? null;

        if (!empty($config)) {
            $syntaxe = $this->syntaxeMigration($config['driver']);

            if ($syntaxe) {
                return $syntaxe->getDatabaseInfo($connection);
            }
        }

        return false;
    }

    public function syntaxeMigration(string|null $driver)
    {
        if ($driver == 'mysql') {
            return new Mysql;
        }

        throw new InvalidArgumentException('error: connection driver not found');
    }

    /**
     * @return array
     */
    private function migrationFiles(string $folder)
    {
        $directory_iterator = new RecursiveDirectoryIterator(directoryRoot($folder), FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::CHILD_FIRST);
        $params = [];

        foreach ($iterator as $info) {
            if ($info->isFile()) {
                $file = $info->getPathname();
                $class = pathinfo($file, PATHINFO_FILENAME);

                $namespace = trim(str_replace([basename($file), directoryRoot(), '/', $folder], ['', '', '\\', ucfirst($folder)], $file), '\\') . '\\' . $class;
                $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

                $params[] = [
                    'migration' => $class,
                    'class' => $namespace
                ];
            }
        }

        return $params;
    }
}
