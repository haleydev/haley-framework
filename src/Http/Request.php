<?php

namespace Haley\Http;

class Request
{
    protected string|null $input = null;

    public function get(string $input, $default = null)
    {
        if (!empty($_GET[$input])) return $this->filterInput($_GET[$input]);

        return $default;
    }

    public function post(string $input, $default = null)
    {
        if (!empty($_POST[$input])) return $this->filterInput($_POST[$input]);

        return $default;
    }

    public function input(string $input, $default = null)
    {
        if ($post = $this->post($input)) return $post;
        elseif ($get = $this->get($input)) return $get;

        return $default;
    }

    public function file(string $input, $default = null)
    {
        if (array_key_exists($input, $_FILES)) return $_FILES[$input];

        return $default;
    }

    public function upload(string $input)
    {
        return new Upload($input);
    }

    public function all()
    {
        $get = $this->filterInput($_GET);
        $post = $this->filterInput($_POST);
        $file = $_FILES ?? [];

        return $this->filterInput(array_merge([], $get, $file, $post));
    }

    public function method()
    {
        $accepted = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'COPY', 'OPTIONS', 'LOCK', 'UNLOCK'];

        $post_method = $this->post('_method');

        if ($post_method) $method = strtoupper($post_method);
        elseif (isset($_SERVER['REQUEST_METHOD'])) $method = $_SERVER['REQUEST_METHOD'];
        else $method = null;

        return in_array(strtoupper($method), $accepted) ? $method : 'GET';
    }

    /**
     * Dominio atual
     * @return string|null
     */
    public function domain()
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }

    public function https()
    {
        if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')
        ) return true;

        return false;
    }

    /**
     * @return string
     */
    public function url(string|null $path = null)
    {
        $http = $this->https() ? 'https://' : 'http://';

        return $http . $_SERVER['HTTP_HOST'] . (!empty($path) ? '/' . trim($path, '/') : '');
    }

    public function urlPath()
    {
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        return '/' . $path;
    }

    /**
     * @return string
     */
    public function urlFull(string|null $path = null)
    {
        $url_path = $this->urlPath();
        return  trim($this->url($url_path) . (!empty($path) ? '/' . trim($path, '/') : ''), '/');
    }

    /**
     * @return string
     */
    public function urlFullQuery(array|null $query = null)
    {
        $url_query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);

        if ($query !== null) {
            $gets = filter_input_array(INPUT_GET, $_GET, FILTER_SANITIZE_SPECIAL_CHARS);

            foreach ($query as $key => $value) $gets[$key] = $value;

            $url_query = http_build_query($gets);
        }

        return $this->urlFull() . (!empty($url_query) ? '?' . $url_query : '');
    }

    public function userAgent()
    {
        return $this->headers('User-Agent');
    }

    /**
     * @return string|null
     */
    public function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (!empty($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];

        return null;
    }

    public function mobile()
    {
        $check = preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|ipad|up\.browser|up\.link|webos|wos)/i",
            $_SERVER["HTTP_USER_AGENT"] ?? ''
        );

        if ($check) return true;

        return false;
    }

    /**
     * @return string
     */
    public function urlQueryReplace(string $url, array $query = [], bool $reset = false)
    {
        $query_string = parse_url($url, PHP_URL_QUERY);
        $query_array = [];

        if (!empty($query_string)) {
            if (!$reset) parse_str($query_string, $query_array);
            $url = str_replace([$query_string, '?'], '', $url);
        }

        foreach ($query as $key => $value) $query_array[$key] = $value;

        $query_build = http_build_query($query_array);

        return $url . (!empty($query_build) ? '?' . $query_build : '');
    }

    /**
     * Busca todos os cabeçalhos HTTP da solicitação atual
     * @return string|array|null
     */
    public function headers(string $header = null)
    {
        if (!function_exists('getallheaders')) return null;

        $headers = getallheaders();

        if (count($headers) == 0) return null;
        elseif ($header == null) return $headers;
        elseif (isset($headers[$header])) return $headers[$header];

        return null;
    }

    public function origin()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) return trim($_SERVER['HTTP_ORIGIN'], '/');
        if (isset($_SERVER['HTTP_REFERER'])) return trim($_SERVER['HTTP_REFERER'], '/');

        return null;
    }

    /**
     * @return mixed|Session
     */
    public function session(string $key = null)
    {
        if ($key !== null) return Session::get($key);

        return new Session;
    }

    protected function filterInput(string|array|null $value)
    {
        if (is_null($value)) return null;

        if (is_string($value)) {
            if ($value == '') return null;
            return $value;
        }

        return array_map(function ($e) {
            if ($e === '') return null;

            if (is_array($e)) return $this->filterInput($e);

            return $e;
        }, $value);
    }
}
