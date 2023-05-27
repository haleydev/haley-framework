<?php

namespace Haley\View;

use Haley\View\Engine\FileEngine;


class View
{
    public function view(string $view, array|object $params = [], string|null $path = null)
    {
        $engine = new FileEngine;

        if ($path === null) $path = directoryRoot('resources/views/');

        $file = $path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.view.php'; 
    
        $view = $engine->getView($file);        

        if(!$view) return;

        foreach ($params as $key => $value) $$key = $value;
        
        require_once $view;  

        return;
    }
}