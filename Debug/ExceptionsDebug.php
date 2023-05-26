<?php

namespace Core\Debug;

use Core\Collections\Config;
use Core\Collections\Memory;

class ExceptionsDebug
{
    public function debug($error)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        if (Memory::get('kernel') == 'console') {
            die(PHP_EOL . "\033[0;31m{$error->getMessage()}\033[0m" . PHP_EOL . PHP_EOL);
        }

        response()->header('content-type', 'text/html; charset=utf-8');

        if (Config::app('debug') == false) {
            return response()->abort(500);
        }

        $error_message = ucfirst($error->getMessage());
        $error_file = $error->getFile();
        $error_line = $error->getLine();
        $error_all = $error;

        $file = file($error_file);
        $analyzer_file = '';

        foreach ($file as $key => $line) {
            $line = str_replace(' ', '&nbsp;', htmlspecialchars($line));
            if ($error_line - 1 == $key) {
                $analyzer_file .= '<p id="error_line" class="error-line"><b class="line-number">' . $key + 1  . '</b>' . $line . '</p>';
            } else {
                $analyzer_file .= '<p><b class="line-number">' . $key + 1 . '</b>' . $line . '</p>';
            }
        }

        $params = [
            'code' => $analyzer_file,
            'error_file' => $error_file,
            'error_message' => $error_message,
            'error_line' => $error_line,
            'error_all' => $error_all,

            'request_all' => request()->all(),
            'method' => request()->method(),
            'headers' => request()->headers(),
        ];

        return view('debug', $params, directoryRoot('core/Debug/resources'));
    }

    public function dd()
    {
    }
}
