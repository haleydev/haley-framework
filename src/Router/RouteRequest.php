<?php

namespace Haley\Router;

class RouteRequest
{
    public function request(false|array $route)
    {
        $this->old();

        if (!empty($route['config']['middleware'])) array_push($route['middleware'], $route['config']['middleware']);

        if (!empty($route['middleware'])) {
            $middlewares = [];

            foreach ($route['middleware'] as $middleware) {
                if (is_array($middleware)) {
                    foreach ($middleware as $value) {
                        $middlewares[] = $value;
                    }
                } else {
                    $middlewares[] = $middleware;
                }
            }

            if (!middleware($middlewares)) return response()->abort(403);
        }

        if (!empty($route['config']['csrf'])) {
            if (!csrf()->check() and $route['config']['csrf']['active'] === true and !in_array('GET', $route['methods'])) return response()->abort(401);
        }

        if ($route['type'] == 'url' and !empty($_GET)) {
            return response()->abort(405);
        }

        if ($route['type'] == 'redirect') {
            return redirect($route['params']['destination'], $route['params']['status']);
        }

        if ($route['type'] == 'view') {
            return view($route['params'][0], $route['params'][1]);
        }

        if ($route['type'] == 'file') {
            return $this->typeFile($route);
        }

        return new RouteAction($route['params']);
    }

    private function typeFile($route)
    {
        $route_params = ROUTER_PARAMS;
        $file_key = array_key_last($route_params);

        foreach ($route_params as $key => $param) {
            if ($key != $file_key) {
                $param = str_replace(['\\', '/', '.',], '', $param);
                if (empty((string)$param)) {
                    unset($route_params[$key]);
                } else {
                    $route_params[$key] = $param;
                }
            }
        }

        $file_params = !empty($route_params) ? DIRECTORY_SEPARATOR .  implode(DIRECTORY_SEPARATOR, $route_params) : '';
        $file = directoryRoot($route['params']['path'] . $file_params);

        if ($route['params']['download']) {
            return response()->download($file);
        } else {
            return response()->file($file);
        }
    }

    private function old()
    {
        request()->session()->replace('FRAMEWORK', ['old' => null]);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $request = request()->all();

            if (!empty($request)) {
                $url = parse_url($_SERVER['HTTP_REFERER']);
                $page = request()->url() . '/' . trim($url['path'] ?? $url['host'] ?? '', '/');
                request()->session()->replace('FRAMEWORK', ['old' => [$page => $request]]);
            }
        }

        return;
    }
}
