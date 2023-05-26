<?php

namespace Core\View;

use Core\View\Engine\FileEngine;
use Core\View\Engine\ViewParams;

class View
{
    public function view(string $view, array|object $params = [], string|null $path = null)
    {
        ViewParams::params($params);

        $engine = new FileEngine;

        if ($path === null) $path = directoryRoot('resources/views/');

        $file = $path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.view.php';

        if($view = $engine->getView($file)) {
            require_once $view;
        }

        return;
    }
}
