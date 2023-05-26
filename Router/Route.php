<?php

namespace Core\Router;

class Route
{
    private static int $group = 0;
    private static array $attributes = [];

    /**
     * @return \Core\Router\RouteOptions
     */
    public static function url(string $route, string|array|callable $action)
    {
        RouteMemory::route($route, $action, ['GET'], 'url');
        return new RouteOptions;
    }

    /**   
     * @return \Core\Router\RouteOptions
     */
    public static function get(string $route, string|array|callable $action)
    {
        RouteMemory::route($route, $action, ['GET'], 'get');
        return new RouteOptions;
    }

    /**  
     * @return \Core\Router\RouteOptions
     */
    public static function post(string $route, string|array|callable $action)
    {
        RouteMemory::route($route, $action, ['POST'], 'post');
        return new RouteOptions;
    }

    /**  
     * @return \Core\Router\RouteOptions
     */
    public static function delete(string $route, string|array|callable $action)
    {
        RouteMemory::route($route, $action, ['DELETE'], 'delete');
        return new RouteOptions;
    }

    /**  
     * @return \Core\Router\RouteOptions
     */
    public static function match(string|array $methods, string $route, string|array|callable $action)
    {
        if (is_string($methods)) $methods = [$methods];

        foreach ($methods as $key => $method) $methods[$key] = strtoupper($method);

        RouteMemory::route($route, $action, $methods, 'match');
        return new RouteOptions;
    }

    /**
     * Retorna um arquivo da pasta private
     */
    public static function file(string $route, string $path, bool $download = false, array $methods = ['GET'])
    {
        RouteMemory::route($route, ['path' => $path, 'download' => $download], $methods, 'file');
        return new RouteOptions;
    }

    /**
     * Redirecionar para uma url
     */
    public static function redirect(string $route, string $destination, int $status = 302)
    {
        RouteMemory::route($route, ['destination' => $destination, 'status' => $status], ['GET'], 'redirect');
        return new RouteOptions;
    }

    /**
     * Renderizar um view diretamente
     * @return \Core\Router\RouteOptions
     */
    public static function view(string $route, string $view, array $params = [])
    {
        RouteMemory::route($route, [$view, $params], ['GET'], 'view');
        return new RouteOptions;
    }

    public static function name(string $value)
    {
        RouteMemory::setAttribute('name', $value);
        self::$attributes[self::$group][] = 'name';

        return new self;
    }

    public static function middleware(string|array $value)
    {
        RouteMemory::setAttribute('middleware', $value);
        self::$attributes[self::$group][] = 'middleware';

        return new self;
    }

    public static function prefix(string $value)
    {
        RouteMemory::setAttribute('prefix', $value);
        self::$attributes[self::$group][] = 'prefix';

        return new self;
    }

    public static function domain(string|array $value)
    {
        RouteMemory::setAttribute('domain', $value);
        self::$attributes[self::$group][] = 'domain';

        return new self;
    }

    public static function namespace(string $value)
    {
        RouteMemory::setAttribute('namespace', trim($value, '\\'));
        self::$attributes[self::$group][] = 'namespace';

        return new self;
    }

    public static function error(callable|array|string $action)
    {
        RouteMemory::setAttribute('error', $action);
        self::$attributes[self::$group][] = 'error';

        return new self;
    }

    public static function group(callable $routes)
    {
        $group = self::$group;

        self::$group++;

        if (is_callable($routes)) call_user_func($routes, $group);

        foreach (self::$attributes[$group] as $name) {
            RouteMemory::removeAttribute($name);
        }

        unset(self::$attributes[$group]);
    }

    /**
     * Encerrar leitura do router
     */
    public static function end()
    {
        return (new RouteResolve)->read(RouteMemory::$routes);
    }
}
