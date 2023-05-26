<?php

namespace Core\Http;

use Core\Collections\HttpCodes;
use Core\Collections\MimeTypes;

class Response
{
    public static function header(string $name, string $value)
    {
        header("$name: $value");
    }

    public static function status(int $status)
    {
        http_response_code($status);
    }

    public static function abort(int $status = 404, string|null $mesage = null)
    {
        response()->status($status);
        response()->header('content-type', 'text/html; charset=utf-8');

        if ($mesage === null) $mesage = HttpCodes::get($status);

        if (defined('ROUTER_NOW')) {
            if ($action = ROUTER_NOW['error']) {
                if (is_string($action)) {
                    $params = explode('::', $action);
                    if (isset($params[0]) and isset($params[1])) {
                        $class = $params[0];
                        $method = $params[1];
                        $rum = new $class;

                        return die($rum->$method($status, $mesage));
                    }
                } elseif (is_array($action)) {
                    $action[0] = new $action[0]();
                    return die(call_user_func($action, $status, $mesage));
                } elseif (is_callable($action)) {
                    return die(call_user_func($action, $status, $mesage));
                }
            }
        }

        if (file_exists(directoryResources('views/error/' . $status . '.view.php'))) {
            return die(view('error.' . $status, [
                'status' => $status,
                'mesage' => $mesage
            ]));
        }

        if (file_exists(directoryResources('views/error/default.view.php'))) {          
            return die(view('error.default', [
                'status' => $status,
                'mesage' => $mesage
            ]));
        }

        if (ob_get_level() > 0) ob_clean();

        return die(self::status($status));
    }

    public static function json(mixed $value)
    {
        header('Content-type: application/json; charset=utf-8');
        return die(print(json_encode($value)));
    }

    public static function download(string $file, string $rename = null)
    {
        if (file_exists($file)) {
            if (ob_get_level() > 0) {
                ob_clean();
            }

            if ($rename == null) {
                $file_name = basename($file);
            } else {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $file_name = $rename . '.' . $extension;
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));

            die(readfile($file));
        }

        return response()->abort(404);
    }

    public static function file(string $file)
    {
        if (file_exists($file)) {
            if (ob_get_level() > 0) {
                ob_clean();
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            header('Content-type: ' . MimeTypes::get($extension));
            header('Content-Length: ' . filesize($file));
            die(readfile($file));
        }

        return response()->abort(404);
    }
}
