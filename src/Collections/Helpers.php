<?php

use Haley\Collections\Password;
use Haley\Env\Env;
use Haley\Exceptions\Debug;
use Haley\Http\Csrf;
use Haley\Http\Redirect;
use Haley\Http\Request;
use Haley\Http\Response;
use Haley\Http\Route;
use Haley\Validator\ValidatorHelper;
use Haley\View\View;

/**
 * @param string $view
 * @param array|object $params
 * @return Template
 */
function view(string $view, array|object $params = [], string|null $path = null)
{
    return (new View)->view($view, $params, $path);
}

/**
 * Redirecionamentos
 */
function redirect(string|null $destination = null, $status = 302)
{
    if ($destination !== null) {
        return (new Redirect)->destination($destination, $status);
    }

    return new Redirect;
}

/**
 * Funções request
 */
function request()
{
    return new Request;
}

/**
 * Funções response
 */
function response()
{
    return new Response;
}

/**
 * Validator helper view
 * @return string|array|false|ValidatorHelper
 */
function validator(string $input = null)
{
    if ($input != null) {
        return (new ValidatorHelper)->first($input);
    }

    return (new ValidatorHelper);
}

/**
 * Retorna o valor declarado em .env
 * @return mixed
 */
function env(string $key = null, mixed $or = null)
{
    return Env::env($key, $or);
}

function password()
{
    return new Password;
}

/**
 * Retorna a URL da rota nomeada.
 * @return Haley\Http\Route|string|null
 */
function route(string|null $name = null, string|array|null ...$params)
{
    if (!empty($params[0])) if (is_array($params[0])) $params = $params[0];

    if ($name !== null) return Route::name($name, $params);

    return new Route;
}

/**
 * Retorna o valor de uma request antiga
 * @return MIXED
 */
function old(string $input = null)
{
    $page = request()->url();
    $session = request()->session('FRAMEWORK');

    if (!empty($session['old'][$page][$input])) {
        return $session['old'][$page][$input];
    }

    return false;
}

function csrf()
{
    return new Csrf;
}

/**
 * @return string
 */
function dd()
{
    $backtrace = debug_backtrace();
    $line = $backtrace[0]['line'] ?? '';
    $file = $backtrace[0]['file'] ?? '';

    return (new Debug)->dd($line, $file, func_get_args());
}

function formatSize(int $bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function middleware(string|array $middlewares)
{
    if (is_string($middlewares)) {
        $middlewares = [$middlewares];
    }

    if (is_array($middlewares) and count($middlewares) > 0) {
        foreach ($middlewares as $middleware) {
            if (str_contains($middleware, '::')) {
                $params = explode('::', $middleware);
            } elseif (str_contains($middleware, '@')) {
                $params = explode('@', $middleware);
            }

            $class = "\App\Middlewares\\{$params[0]}";
            $rum = new $class;
            $rum->{$params[1]}();

            if ($rum->response == false) {
                return false;
            }
        }
    }

    return true;
}

function directorySeparator(string $directory)
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory);
}

function directoryPrivate(string|null $path = null)
{
    if ($path === null) return DIRECTORY_PRIVATE;

    return DIRECTORY_PRIVATE . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
}

function directoryPublic(string|null $path = null)
{
    if ($path === null) return DIRECTORY_PUBLIC;

    return DIRECTORY_PUBLIC . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
}

function directoryResources(string|null $path = null)
{
    if ($path === null) return DIRECTORY_RESOURCES;

    return DIRECTORY_RESOURCES . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
}

function directoryRoot(string|null $path = null)
{
    if ($path === null) return DIRECTORY_ROOT;

    return DIRECTORY_ROOT . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
}

function directoryHaley(string|null $path = null)
{
    if ($path === null) return DIRECTORY_HALEY;

    return DIRECTORY_HALEY . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
}

function createDir(string $path)
{
    if (file_exists($path)) return true;

    $path = directorySeparator($path);

    return file_exists($path) ? true : mkdir($path, 0777, true);
}

function deleteDir(string $path)
{
    $path = directorySeparator($path);

    if (!file_exists($path)) return true;

    $directory_iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($iterator as $file) {
        $file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
    }

    return rmdir($path);
}

function deleteFile(string $path)
{
    $path = directorySeparator($path);

    return file_exists($path) ? unlink($path) : true;
}

function executeCallable(string|array|callable $callable, array $args = [], string|null $namespace = null)
{
    $callback = null;

    $namespace = !empty($namespace) ? $namespace . '\\' : '';

    if (is_string($callable)) {
        if (str_contains($callable, '::')) {
            $params = explode('::', $callable);
        } elseif (str_contains($callable, '@')) {
            $params = explode('@', $callable);
        }

        if (isset($params[0]) and isset($params[1])) {
            $callable = [];
            $class = $namespace . $params[0];
            $callable[0] = new $class;
            $callable[1] = $params[1];
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
            $callback = $callable;
        }
    } elseif (is_array($callable)) {
        $callable[0] = new $callable[0];
        $reflection = new ReflectionMethod($callable[0], $callable[1]);
        $callback = $callable;
    } elseif (is_callable($callable)) {
        $callback = $callable;
        $reflection = new ReflectionFunction($callback);
    }

    if (is_callable($callback)) {
        $parameters = $reflection->getParameters();
        $args_valid = [];

        foreach ($parameters as $value) {
            $arg = $value->getName();

            if (array_key_exists($arg, $args)) {
                $args_valid[$arg] = $args[$arg];
            }
        }

        return call_user_func_array($callback, $args_valid);
    }

    return null;
}

// /**
//  * @return array|null
//  */
// function classInfo(string $file)
// {
//     if (file_exists($file)) {
//         require_once $file;
//         // Obtém todas as classes declaradas no arquivo

//         $all = get_declared_classes();
//         $class = end($all);       
    
//         if($class) {        
//             $info = [];
//             $reflection = new ReflectionClass($class);

//             $info['class'] = $reflection->getName();            
//             $info['namespace'] = $reflection->getNamespaceName();
//             $info['file'] = $reflection->getFileName();           
//             $info['properties'] = $reflection->getProperties();  
//             $info['methods'] = $reflection->getMethods();

//             return $reflection;           
//         }
//     }

//     return null;
// }
