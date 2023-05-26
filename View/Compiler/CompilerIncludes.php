<?php
namespace Core\View\Compiler;

class CompilerIncludes
{
    public array|false $files = false;
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
        $regex = "/@extends\((.*?)\)/s";
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
        $file = ROOT . '/resources/views/' . $value . '.view.php';

        if (file_exists($file)) {
            if(!is_array($this->files)) {
                $this->files = [];
            }

            $this->files[$file] = filemtime($file);
            $include = file_get_contents($file);
            $limit = 1;

            $this->view = str_replace($query, $include, $this->view, $limit);
        } else {
            $this->view = str_replace($query, '', $this->view, $limit);
        }

        return $this->loop();
    }
}
