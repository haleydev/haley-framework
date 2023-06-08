<?php

namespace Haley\Router;

class RouteAction
{
    public function __construct($action)
    {
        if (is_string($action)) return $this->string($action);
        if (is_array($action)) return $this->array($action);
        if (is_callable($action)) return $this->callable($action);
    }

    private function string(string $action)
    {
        if (str_contains($action, '::')) {
            $params = explode('::', $action);
        } else if (str_contains($action, '@')) {
            $params = explode('@', $action);
        }

        $namespace = '';

        if (defined('ROUTER_NOW')) {
            if (!empty(ROUTER_NOW['namespace'])) $namespace = ROUTER_NOW['namespace'] . '\\';
        }

        if (isset($params[0]) and isset($params[1])) {
            $class = $namespace . $params[0];
            $method = $params[1];
            $rum = new $class;

            return $this->result($rum->$method());
        }

        return response()->abort(500);
    }

    private function array(array $action)
    {
        $action[0] = new $action[0]();

        if (is_callable($action)) return $this->result(call_user_func($action));

        return response()->abort(500);
    }

    private function callable(callable $action)
    {
        if (is_callable($action)) return $this->result(call_user_func($action));

        return response()->abort(500);
    }

    private function result(mixed $value)
    {
        if (is_string($value) || is_numeric($value)) echo $value;

        if (is_array($value) || is_object($value)) {
            if (!is_callable($value)) print(json_encode($value));
        }

        die;
    }
}
