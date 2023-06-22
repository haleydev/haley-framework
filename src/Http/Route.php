<?php

namespace Haley\Http;

class Route
{
    /**
     * @return string|null
     */
    public static function name(string $name, string|array|null ...$params)
    {
        if (!empty($params[0])) {
            if (is_array($params[0])) $params = $params[0];
        }

        if (defined('ROUTER_NAMES') and !empty($params)) {
            if (isset(ROUTER_NAMES[$name])) {
                $route = ROUTER_NAMES[$name];

                if (count($params)) {
                    if (preg_match_all('/\{\?\}/', $route, $matches)) {
                        if (!empty($matches[0])) {
                            foreach ($matches[0] as $key => $value) {
                                if ($position = strpos($route, $value)) {
                                    $route = substr_replace($route, !empty($params[$key]) ? $params[$key] : '', $position, strlen($value));
                                }
                            }
                        }
                    };
                }

                $route = str_replace(['/{?}', '{?}'], '', $route);

                return request()->url($route);
            }
        }

        return null;
    }

    /**
     * @return string|array|null
     */
    public static function params(string|null $param = null)
    {
        if (defined('ROUTER_PARAMS') and !empty(ROUTER_PARAMS)) {
            if ($param == null) return ROUTER_PARAMS;

            if (array_key_exists($param, ROUTER_PARAMS)) {
                return ROUTER_PARAMS[$param];
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    public static function now()
    {
        if (defined('ROUTER_NOW')) return ROUTER_NOW;

        return null;
    }
}
