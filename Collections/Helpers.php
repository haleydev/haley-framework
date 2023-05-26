<?php

use Core\Collections\Password;
use Core\Env\Env;
use Core\Http\Csrf;
use Core\Http\Redirect;
use Core\Http\Request;
use Core\Http\Response;
use Core\Validator\ValidatorHelper;
use Core\View\View;

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
    if ($destination != null) {
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
 * Retorna o valor do parâmetro passado em router
 * @return string|null
 */
function param(string $param)
{
    return Request::param($param);
}

/**
 * Retorna a URL da rota nomeada.
 * @return string|null
 */
function route(string $name, string|array $params = [])
{
    return Request::route($name, $params);
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
 * @return var_dump
 */
function dd()
{
    $all = func_get_args();   

    foreach ($all as $value) {
        echo "<pre>" . PHP_EOL;
        var_dump($value);
        echo "</pre>";
    }
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
            $params = explode('::', $middleware);

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
    if ($path === null) return ROOT;

    return ROOT . DIRECTORY_SEPARATOR . trim(directorySeparator($path), DIRECTORY_SEPARATOR);
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
