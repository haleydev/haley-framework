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

        return new RouteAction($route['params']);
    }

    private function old()
    {
        request()->session()->replace('HALEY', ['old' => null]);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $request = request()->all();

            if (!empty($request)) {
                $url = parse_url($_SERVER['HTTP_REFERER']);
                $page = request()->url($url['path'] ?? $url['host'] ?? null);
                request()->session()->replace('HALEY', ['old' => [$page => $request]]);
            }
        }

        return;
    }
}
