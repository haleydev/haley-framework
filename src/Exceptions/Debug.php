<?php

namespace Haley\Exceptions;

use Haley\Collections\Config;
use Haley\Collections\Memory;
use Haley\Kernel;
use Haley\Shell\Shell;

class Debug
{
    protected int $space = 0;
    protected string $dd = '';
    protected array $tokens = [];

    public function exceptions($error)
    {
        if (!defined('HALEY_STOP')) define('HALEY_STOP', microtime(true));
        if (ob_get_level() > 0) ob_clean();

        $error_message = ucfirst($error->getMessage());
        $error_file = $error->getFile();
        $error_line = $error->getLine();
        $error_all = $error;

        // get_included_files(), get_required_files(), get_include_path()

        if (Memory::get('kernel') == 'console') {
            Shell::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();

            (new Kernel)->terminate();
        }

        response()->header('content-type', 'text/html; charset=utf-8');

        if (Config::app('debug') == false) return response()->abort(500);

        response()->status('500');

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

        view('exceptions', $params, directoryHaley('Exceptions/views'));

        (new Kernel)->terminate();
    }

    public function dd(int $line, string $file, $values)
    {
        if (!defined('HALEY_STOP')) define('HALEY_STOP', microtime(true));
        if (ob_get_level() > 0) ob_clean();

        response()->header('content-type', 'text/html; charset=utf-8');

        if (!count($values)) $values = [null];

        $this->dd .= "<div class=\"dd-title\">$file - $line</div>";

        foreach ($values as $value) {
            $this->dd .= "<div class=\"dd-code\">";
            $this->ddValues($value);
            $this->dd .= '</div>';
        }

        view('dd', ['dd' => $this->dd], directoryHaley('Exceptions/views'));

        (new Kernel)->terminate();
    }

    protected function ddValues($value, string|null $array_name = null)
    {
        $type = gettype($value);
        $hidden_class = implode(' ', $this->tokens);

        if ($array_name === null) {
            $key = '';
        } elseif (is_numeric($array_name)) {
            $key = '<span class="dd-code-array-key ' . $hidden_class . '">' . $array_name . '</span><span class="dd-code-arrow"> : </span>';
        } else {
            $key = '<span class="dd-code-array-key ' . $hidden_class . '">"' . $array_name . '"</span><span class="dd-code-arrow"> : </span>';
        }

        if ($value === null) {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-var ' . $hidden_class . '">' . $key . 'null</p>';
            return;
        }

        if ($value === false) {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-var ' . $hidden_class . '">' . $key . 'false</p>';
            return;
        }

        if ($value === true) {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-var ' . $hidden_class . '">' . $key . 'true</p>';
            return;
        }

        if (is_callable($value)) {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-var ' . $hidden_class . '">' . $key . print_r($value, true) . '</p>';
            return;
        }

        if (is_numeric($value) and $type !== 'string') {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-var ' . $hidden_class . '">' . $key . $value . '</p>';
            return;
        }

        if (is_string($value)) {
            $this->dd .= '<p title="' . gettype($value) . '"' . $this->ddSpace() . ' class="dd-code-string-value ' . $hidden_class . '">' . $key . '"' . htmlspecialchars($value) . '"</p>';
            return;
        }

        if (is_object($value)) $value = (array)$value;

        if (is_array($value)) {
            $token = 'dd' . bin2hex(random_bytes(6));
            $this->tokens[] = $token;

            $this->dd .= '<p class="' . $hidden_class . '" title="' . $type . '"' . $this->ddSpace() . '>' . $key . '<span  data-token="' . $token . '" class="dd-code-type">' . $type . ':' . count($value) . ' </span><span class="dd-code-tags ' . $hidden_class . '">{</span></p>';
            $this->space++;

            foreach ($value as $key => $value) {
                $this->ddValues($value, $key);
            }

            $last_token = array_key_last($this->tokens);
            unset($this->tokens[$last_token]);

            $this->space--;
            $this->dd .= '<p ' . $this->ddSpace() . ' ><span class="dd-code-tags ' . $hidden_class . '">}</span></p>';
        }
    }

    protected function ddSpace()
    {
        $margin = $this->space * 20;
        return "style=\"margin-left: {$margin}px;\"";
    }
}
