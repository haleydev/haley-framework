<?php

namespace Haley\Router;

class RouteResolve
{
    private string $url;
    private string $method;
    private string|null $domain;
    private array $params = [];
    private array $names = [];

    public function __construct()
    {
        $this->url = trim(filter_var(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), FILTER_SANITIZE_URL), '/');
        $this->method = request()->method();
        $this->domain = request()->domain();
    }

    public function read(array $routes)
    {
        $route_valid = null;

        foreach ($routes as $route) {
            if (!empty($route['name'])) {
                $this->names[$route['name']] = preg_replace('/{(.*?)}/', '{!???!}', $route['route']);
            }

            $params = $this->routeParams($route['route']);

            if (in_array($this->method, $route['methods']) and $params == true) {
                if (count($route['domain'])) {
                    if (in_array($this->domain, $route['domain'])) $route_valid = $route;
                } else {
                    $route_valid = $route;
                }
            }
        }

        if (empty($route_valid)) return response()->abort(404);

        define('ROUTER_PARAMS', $this->params);
        define('ROUTER_NAMES', $this->names);
        define('ROUTER_NOW', $route_valid);

        return (new RouteRequest)->request($route_valid);
    }

    private function routeParams(string $route)
    {
        $route = trim($route, '/');
        $check = $route;
        $params = [];

        if (preg_match('/{(.*?)}/', $route)) {
            $array_route = explode('/', $route);
            $array_url = explode('/', $this->url);

            foreach ($array_route as $key => $value) {
                if (preg_match('/{(.*?)}/', $value, $math)) {
                    if (isset($array_url[$key])) {
                        $param = str_replace(['?}', '{', '}'], '', $math[0]);
                        $params[$param] = $array_url[$key];

                        $check = str_replace($math[0], $array_url[$key], $check);
                    } elseif (substr($value, -2) == '?}') {
                        $check = str_replace("/$math[0]", '', $check);
                    }
                }
            }
        }

        if ($check == $this->url) {
            $this->params = $params;

            return true;
        }

        return false;
    }
}
