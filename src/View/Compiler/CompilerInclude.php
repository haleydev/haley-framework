<?php

namespace Haley\View\Compiler;

use Exception;

class CompilerInclude
{
    public array|false $include = false;
    private $view;

    public function run($view)
    {
        $this->view = $view;
        $loop = $this->loop();

        if ($loop == true) {
            return $this->view;
        }

        return false;
    }

    protected function loop()
    {
        $regex = "/@include\((.*?)\)/s";
        if (preg_match($regex, $this->view, $matches)) {
            return $this->include($matches);
        }

        return true;
    }

    protected function include($matches)
    {
        $query = $matches[0];
        $value = str_replace(['\'', '"', ' ', '.view.php'], '', $matches[1]);
        $value = str_replace(['.'], DIRECTORY_SEPARATOR, trim($value, '.'));
        $file = directoryRoot('resources/views/' . $value . '.view.php');

        if (file_exists($file)) {
            if (!is_array($this->include)) {
                $this->include = [];
            }

            $this->include[$file] = filemtime($file);
            $include = file_get_contents($file);
            $limit = 1;

            $this->view = str_replace($query, $include, $this->view, $limit);
        } else {
            throw new Exception('View not found @include(' . $matches[1] . ')');
        }

        return $this->loop();
    }
}
