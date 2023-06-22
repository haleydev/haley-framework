<?php

namespace Haley\Router;

class RouteAction
{
    public function __construct(string|array|callable|null $action)
    {
        $namespace = null;
        $args = [];

        if (defined('ROUTER_NOW')) {
            if (!empty(ROUTER_NOW['namespace'])) $namespace = ROUTER_NOW['namespace'] . '\\';
        }

        if (defined('ROUTER_PARAMS')) {
            if (!empty(ROUTER_PARAMS)) $args = ROUTER_PARAMS;
        }

        return $this->result(executeCallable($action, $args, $namespace));

        // if (is_string($action)) return $this->string($action);
        // if (is_array($action)) return $this->array($action);
        // if (is_callable($action)) return $this->callable($action);
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
