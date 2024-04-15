<?php

namespace Haley\Console\Commands;

use Haley\Collections\Molds;
use Haley\Console\Lines;
use Haley\Database\Query\DB;

class CommandCreate
{
    public function model(string $name, string|null $connection = null)
    {
        $helper = DB::helper($connection);
        $all = strtolower($name) == '--all';

        if ($all) {
            $names = $helper->table()->getNames() ?? [];
        } else {
            $names = [$name];
        }

        if (count($names)) {
            foreach ($names as $name) {
                $params = $this->params($name, directoryRoot('app/Models'));

                if (file_exists($params['file_directory'])) {
                    if ($all) continue;

                    $response = Lines::red('replace current file ? (y/n)')->readline();

                    if ($response != 'y') {
                        Lines::red('operation canceled')->br();
                        return;
                    }
                }

                $primary = null;
                $columns = [];

                if ($helper->table()->has($name)) {
                    $primary = $helper->constraint()->getPrimaryKey($name);
                    $columns = $helper->column()->getNames($name);
                }

                $mold = Molds::model($params['class'], $name, $primary, $columns, $params['namespace']);

                createDir($params['folder']);

                file_put_contents($params['file_directory'], $mold);

                if (file_exists($params['file_directory'])) {
                    Lines::green("model {$params['class']} created")->normal($params['file_directory'])->br();
                } else {
                    Lines::red('error: failed to create model')->br();
                }
            }
        }
    }

    public function job(string $name)
    {
        $params = $this->params($name, directoryRoot('app/Jobs'));

        if (file_exists($params['file_directory'])) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        $mold = Molds::job($params['class'], $params['namespace']);

        createDir($params['folder']);

        file_put_contents($params['file_directory'], $mold);

        if (file_exists($params['file_directory'])) {
            Lines::green("job {$params['class']} created")->normal($params['file_directory'])->br();
        } else {
            Lines::red('error: failed to create job')->br();
        }
    }

    public function controller(string $name)
    {
        $params = $this->params($name, directoryRoot('app/Controllers'));

        if (file_exists($params['file_directory'])) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        $mold = Molds::controller($params['class'], $params['namespace']);

        createDir($params['folder']);

        file_put_contents($params['file_directory'], $mold);

        if (file_exists($params['file_directory'])) {
            Lines::green("controller {$params['class']} created")->normal($params['file_directory'])->br();
        } else {
            Lines::red('error: failed to create controller')->br();
        }
    }

    public function class(string $name)
    {
        $params = $this->params($name, directoryRoot('app/Classes'));

        if (file_exists($params['file_directory'])) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        $mold = Molds::class($params['class'], $params['namespace']);

        createDir($params['folder']);

        file_put_contents($params['file_directory'], $mold);

        if (file_exists($params['file_directory'])) {
            Lines::green("class {$params['class']} created")->normal($params['file_directory'])->br();
        } else {
            Lines::red('error: failed to create class')->br();
        }
    }

    public function database(string $name)
    {
        $table = pathinfo(directoryRoot('database/' . $name), PATHINFO_FILENAME);

        $class = date('Y_m_d_His') . '_' . $table;
        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';
        $location = directoryRoot('database/' . str_replace(basename($name), '', $name) . $class . '.php');
        $mold = (new Molds)->database($class, $namespace, $table);

        createDir(dirname($location));

        if (file_exists($location)) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        file_put_contents($location, $mold);

        if (file_exists($location)) {
            Lines::green("database migration {$class} created")->normal($location)->br();
        } else {
            Lines::red('error: failed to create database migration class');
        }
    }

    public function middleware(string $name)
    {
        $params = $this->params($name, directoryRoot('app/Middlewares'));

        if (file_exists($params['file_directory'])) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        $mold = Molds::middleware($params['class'], $params['namespace']);

        createDir($params['folder']);

        file_put_contents($params['file_directory'], $mold);

        if (file_exists($params['file_directory'])) {
            Lines::green("middleware {$params['class']} created")->normal($params['file_directory'])->br();
        } else {
            Lines::red('error: failed to create middleware')->br();
        }
    }

    public function env()
    {
        $location = directoryRoot('.env');
        $mold = (new Molds)->env();

        if (file_exists($location)) {
            $response = Lines::red('replace current file ? (y/n)')->readline();

            if ($response != 'y') {
                Lines::red('operation canceled')->br();
                return;
            }
        }

        file_put_contents($location, $mold);

        if (file_exists($location)) {
            Lines::green("env created")->normal($location)->br();
        } else {
            Lines::red('error: failed to create env')->br();
        }
    }

    private function params(string $name, string $directory)
    {
        $name = trim($name, '/');
        $folder = '';
        $namespace = '';

        $folder_explode = explode('/', $name);
        $key = array_key_last($folder_explode);

        foreach ($folder_explode as $key => $value) $folder_explode[$key] = ucfirst($value);

        if ($key > 0) {
            $name = $folder_explode[$key];
            unset($folder_explode[$key]);

            $folder = implode('/', $folder_explode) . '/';
            $namespace = '\\' . implode('\\', $folder_explode);
        }

        $class = explode('_', $name);
        foreach ($class as $key => $value) $class[$key] = ucfirst($value);
        $class = implode('', $class);

        $file_name = $class . '.php';
        $file_directory = $directory . '/' . $folder . $file_name;

        return [
            'class' => $class,
            'namespace' => $namespace,
            'file_directory' => $file_directory,
            'file_name' => $file_name,
            'folder' => $directory . '/' . $folder
        ];
    }
}
