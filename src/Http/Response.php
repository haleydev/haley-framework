<?php

namespace Haley\Http;

use Haley\Collections\HttpCodes;
use Haley\Collections\MimeTypes;

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
        if (ob_get_level() > 0) ob_clean();

        response()->status($status);
        response()->header('content-type', 'text/html; charset=utf-8');

        if ($mesage === null) $mesage = HttpCodes::get($status);

        if (defined('ROUTER_NOW')) {
            if ($action = ROUTER_NOW['error']) {
                return executeCallable($action, [
                    'status' => $status,
                    'mesage' => $mesage
                ]);
            }
        }

        if (file_exists(directoryResources('views/error/' . $status . '.view.php'))) {
            return view('error.' . $status, [
                'status' => $status,
                'mesage' => $mesage
            ]);
        }

        if (file_exists(directoryResources('views/error/default.view.php'))) {
            view('error.default', [
                'status' => $status,
                'mesage' => $mesage
            ]);
        }

        return self::status($status);
    }

    public static function json(mixed $value)
    {
        if (ob_get_level() > 0) ob_clean();

        header('Content-type: application/json; charset=utf-8');
        print(json_encode($value));
    }

    public static function download(string $file, string $rename = null)
    {
        if (file_exists($file)) {
            if (ob_get_level() > 0) ob_clean();

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

            readfile($file);

            return;
        }

        return response()->abort(404);
    }

    public static function file(string $file)
    {
        if (file_exists($file)) {
            if (ob_get_level() > 0) ob_clean();

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            header('Content-type: ' . MimeTypes::get($extension));
            header('Content-Length: ' . filesize($file));

            readfile($file);

            return;
        }

        return response()->abort(404);
    }
}
