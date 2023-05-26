<?php

namespace Core\Http;

class Request
{
    /**
     * Retorna o valor do parâmetro passado em router.
     * @return string|false
     */
    public static function param(string $param)
    {
        if (defined('ROUTER_PARAMS') and !empty(ROUTER_PARAMS)) {
            if (array_key_exists($param, ROUTER_PARAMS)) {
                return urldecode(ROUTER_PARAMS[$param]);
            }
        }

        return null;
    }

    /**
     * Retorna a url da rota nomeada
     * @return string|false
     */
    public static function route(string $name, string|array $params = [])
    {
        if (is_string($params)) {
            $params = explode('/', $params);
        }

        if (defined('ROUTER_NAMES')) {
            if (isset(ROUTER_NAMES[$name])) {
                $route = ROUTER_NAMES[$name];

                foreach ($params as $key => $param) {
                    if (!empty($param)) {
                        if (str_contains($route, '{!???!}')) {
                            $route = preg_replace('/{!???!}/', $param, $route, 1);
                        } elseif ($key == 0) {
                            $route .= $param;
                        } else {
                            $route .= '/' . $param;
                        }
                    }
                }

                $route = str_replace(['/{!???!}', '{!???!}'], '', $route);

                return rtrim(env('APP_URL') . '/' . $route, '/');
            }
        }

        return false;
    }

    public static function get(string $input)
    {
        if (isset($_GET[$input])) {
            if (!empty((string)$_GET[$input])) {
                return filter_input(INPUT_GET, $input, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return false;
    }

    public static function post(string $input)
    {
        if (isset($_POST[$input])) {
            if (!empty($_POST[$input])) {
                return filter_input(INPUT_POST, $input, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return false;
    }

    public static function file(string $input)
    {
        $file = $_FILES;

        if (isset($file[$input])) {
            if (is_array($file[$input]['name'])) {
                if (!empty($file[$input]['name'][0])) {
                    return $file[$input];
                }
            }

            if (is_string($file[$input]['name'])) {
                if (!empty($file[$input]['name'])) {
                    return $file[$input];
                }
            }
        }

        return false;
    }

    public static function upload(string $input)
    {
        return (new Upload)->input($input);
    }

    public static function all()
    {
        $get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $file = $_FILES ?? [];

        return array_merge([], $get, $file, $post);
    }

    public static function input(string $input)
    {
        if (array_key_exists($input, self::all())) {
            return self::all()[$input];
        }

        return false;
    }

    public static function method()
    {
        $accepted = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'COPY', 'OPTIONS', 'LOCK', 'UNLOCK'];

        $post_method = self::post('_method');

        if ($post_method) {
            $method = strtoupper($post_method);
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        } else {
            $method = false;
        }

        return in_array(strtoupper($method), $accepted) ? $method : false;
    }

    /**
     * Dominio atual
     * @return string|null
     */
    public static function domain()
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }

    /**
     * @return string
     */
    public static function url(string|null $path = null)
    {
        $http = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
        return $http . $_SERVER['HTTP_HOST'] . (!empty($path) ? '/' . trim($path, '/') : '');
    }

    /**
     * @return string
     */
    public static function urlFull(string|null $path = null)
    {
        $url_path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        return self::url(!empty($url_path) ? $url_path : null) . (!empty($path) ? '/' . trim($path, '/') : '');
    }

    /**
     * @return string
     */
    public static function urlFullQuery(array|null $query = null)
    {
        $url_query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);

        if ($query !== null) {
            $gets = filter_input_array(INPUT_GET, $_GET, FILTER_SANITIZE_SPECIAL_CHARS);

            foreach ($query as $key => $value) $gets[$key] = $value;

            $url_query = http_build_query($gets);
        }

        return self::urlFull() . (!empty($url_query) ? '?' . $url_query : '');
    }

    /**
     * @return string
     */
    public static function urlQueryReplace(string $url, array $query = [], bool $reset = false)
    {
        $query_string = parse_url($url, PHP_URL_QUERY);
        $query_array = [];

        if (!empty($query_string)) {
            if(!$reset) parse_str($query_string, $query_array);
            $url = str_replace([$query_string, '?'], '', $url);
        }

        foreach ($query as $key => $value) $query_array[$key] = $value;

        $query_build = http_build_query($query_array);

        return $url . (!empty($query_build) ? '?' . $query_build : '');
    }

    /**
     * Busca todos os cabeçalhos HTTP da solicitação atual
     * @return false|string|array
     */
    public function headers(string $header = null)
    {
        if (!function_exists('getallheaders')) {
            return false;
        }

        $headers = getallheaders();

        if (count($headers) == 0) {
            return false;
        } elseif ($header == null) {
            return $headers;
        } elseif (isset($headers[$header])) {
            return $headers[$header];
        }

        return false;
    }

    /**
     * @return mixed|Session
     */
    public static function session(string $key = null)
    {
        if ($key != null) {
            return Session::get($key);
        }

        return new Session;
    }
}
