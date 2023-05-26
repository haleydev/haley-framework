<?php

namespace Haley\Router;

class RouteOptions
{
    public function name(string $value)
    {
        $key = array_key_last(RouteMemory::$routes);

        if (empty(RouteMemory::$routes[$key]['name'])) {
            RouteMemory::$routes[$key]['name'] = $value;
        } else {
            RouteMemory::$routes[$key]['name'] .= '.' . $value;
        }

        return $this;
    }

    public function middleware(string|array $value)
    {
        $key = array_key_last(RouteMemory::$routes);
        RouteMemory::$routes[$key]['middleware'][] = $value;

        return $this;
    }

    public function domain(string $value)
    {
        $key = array_key_last(RouteMemory::$routes);
        RouteMemory::$routes[$key]['domain'][] = $value;

        return $this;
    }
}
