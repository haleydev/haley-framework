<?php
namespace Core\Console\Commands;

use Core\Collections\Log;
use Core\Collections\Molds;
use Core\Console\Lines;
use Core\Database\Migrations\Migration;
use PDOException;

class Command_Create extends Lines
{
    public function createModel($params)
    {
        try {
            $database = (new Migration)->modelInfo();
        } catch (PDOException $error) {          
            Log::create('database', $error->getMessage());
            return $this->red($error->getMessage());
        }
      
        $db_table = null;

        if ($database) {
            if ($params == '--all') {
                createDir(directoryRoot('app/models'));
                $count = 0;
                foreach ($database as $data) {
                    $location = directoryRoot("app/models/{$data['table']}.php");
                    $mold = (new Molds)->model($data['table'], $data['table'], $data['primary'], $data['columns'], '');

                    if (!file_exists($location)) {
                        file_put_contents($location, $mold);
                        $this->green("model {$data['table']} created");

                        if (file_exists($location)) {
                            $count++;
                        }
                    }
                }

                if ($count == 0) {
                    $this->green('no model pending');
                }

                return;
            } else {
                $check_table = pathinfo($params, PATHINFO_FILENAME);
                foreach ($database as $data) {
                    if ($data['table'] == $check_table) {
                        $db_table = $data;
                    }
                }
            }
        }

        $location = directoryRoot("app/models/$params.php");
        $class = pathinfo($location, PATHINFO_FILENAME);

        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response != 'y') {
                return $this->green('operation canceled');
            }
        }

        $namespace = trim(str_replace([basename($params), '/'], ['', '\\'], $params), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

        if (empty($db_table)) {
            $mold = (new Molds)->model($class, $class, null, [], $namespace);
        } else {
            $mold = (new Molds)->model($class, $class, $db_table['primary'], $db_table['columns'], $namespace);
        }

        file_put_contents($location, $mold);

        if (file_exists($location)) {
            return $this->green("model $params created");
        }

        return  $this->red('error: failed to create model');
    }

    public function createJob(string $name)
    {
        $location = directoryRoot("/app/Jobs/$name.php");
        $class = pathinfo($location, PATHINFO_FILENAME);

        if (empty($class)) {
            return $this->red('error: invalid file name');
        }

        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';
      
        $mold = (new Molds)->job($class, $namespace);

        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }

    public function createController(string $name)
    {
        $location = directoryRoot("app/Controllers/$name.php");
        $class = pathinfo($location, PATHINFO_FILENAME);

        if (empty($class)) {
            return $this->red('error: invalid file name');
        }

        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

        $mold = (new Molds)->controller($class, $namespace);

        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }

    public function createClass(string $name)
    {
        $location = directoryRoot("app/Classes/$name.php");
        $class = pathinfo($location, PATHINFO_FILENAME);

        if (empty($class)) {
            return $this->red('error: invalid file name');
        }

        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

        $mold = (new Molds)->class($class, $namespace);

        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }

    public function createDatabase(string $name)
    {
        $table = pathinfo('/database/' . $name, PATHINFO_FILENAME);

        if (empty($table)) {
            return $this->red('error: invalid file name');
        }

        $class = $table . '_' . date('YmdHis');
        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';
        $location = directoryRoot('/database/' . str_replace(basename($name), '', $name) . $class . '.php');
       

        $mold = (new Molds)->database($class, $namespace, $table);
    
        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }

    public function createMiddleware(string $name)
    {
        $location = directoryRoot("/app/Middlewares/$name.php");
        $class = pathinfo($location, PATHINFO_FILENAME);

        if (empty($class)) {
            return $this->red('error: invalid file name');
        }

        $namespace = trim(str_replace([basename($name), '/'], ['', '\\'], $name), '\\');
        $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

        $mold = (new Molds)->middleware($class, $namespace);

        createDir(dirname($location));

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }

    public function createEnv()
    {
        $location = directoryRoot('.env');
        $mold = (new Molds)->env();

        if (file_exists($location)) {
            $this->red('replace current file ? (y/n)', false);
            $response = $this->readline();

            if ($response == 'y') {
                file_put_contents($location, $mold);
            } else {
                return $this->green('operation canceled');
            }
        } else {
            file_put_contents($location, $mold);
        }

        if (file_exists($location)) {
            return $this->green('success');
        }

        $this->red('error: failed to create file');
    }
}
