<?php

namespace Haley\Router;

class RouteMemory
{
    public static array $routes = [];
    public static array|null $config = null;

    private static array $attributes = [
        'name' => [],
        'middleware' => [],
        'prefix' => [],
        'domain' => [],
        'namespace' => [],
        'error' => []
    ];

    public static function route(string $route, mixed $params, array $methods, string $type)
    {
        $route = trim($route, '/');
        $name = null;
        $prefix = null;
        $namespace = null;
        $domain = [];

        if (count(self::$attributes['namespace'])) $namespace = implode('\\', self::$attributes['namespace']);

        if (count(self::$attributes['name'])) $name = implode('.', self::$attributes['name']);

        if (count(self::$attributes['prefix'])) {
            $prefix = implode('/', self::$attributes['prefix']);
            $route = $prefix . '/' . $route;
        }

        if (!empty(self::$config['prefix'])) $route = trim(self::$config['prefix'], '/') . '/' . $route;

        if (count(self::$attributes['domain'])) {
            foreach (self::$attributes['domain'] as $value) {
                if (is_string($value)) {
                    $domain[] = $value;
                } elseif (is_array($value)) {
                    foreach ($value as $e) {
                        if (!empty($e) && is_string($e)) $domain[] = $e;
                    }
                }
            }
        }

        self::$routes[] = [
            'route' => $route,
            'params' => $params,
            'methods' => $methods,
            'type' => $type,
            'config' => self::$config,

            'name' => $name,
            'middleware' => self::$attributes['middleware'],
            'prefix' => $prefix,
            'domain' => $domain,
            'namespace' => $namespace,
            'error' => end(self::$attributes['error']) ?? null
        ];
    }

    public static function setAttribute(string $name, mixed $value)
    {
        self::$attributes[$name][] = $value;
    }

    public static function removeAttribute(string $name)
    {
        if (!count(self::$attributes[$name])) return;

        $key = array_key_last(self::$attributes[$name]);

        if ($key !== null) unset(self::$attributes[$name][$key]);
    }

    public static function resetAttributes()
    {
        self::$config = null;

        self::$attributes = [
            'name' => [],
            'middleware' => [],
            'prefix' => [],
            'domain' => [],
            'namespace' => [],
            'error' => []
        ];
    }
}
