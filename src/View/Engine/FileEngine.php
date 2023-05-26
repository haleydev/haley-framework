<?php

namespace Core\View\Engine;

use Core\View\Compiler\CompilerIncludes;
use Core\View\Compiler\CompilerPHP;
use Core\View\Compiler\CompilerSections;
use Exception;

class FileEngine
{
    private false|string $template_file = false;
    private false|string $template_cache = false;

    public function getView(string $file)
    {
        if (!file_exists($file)) throw new Exception('File not found: ' . $file);        

        $this->template_file = $file;

        $this->checkCache();
        
        $params = ViewParams::$params;

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }

        return require_once $this->template_cache;
    }   

    protected function compilerExecute()
    {
        $template_file = $this->template_file;
        $template_name = basename($template_file);
        $folder_cache = directoryRoot('storage/cache/views' . rtrim(str_replace([directoryRoot('resources/views'), $template_name], '', $template_file), DIRECTORY_SEPARATOR));

        createDir($folder_cache);

        // view file
        $view = file_get_contents($this->template_file);

        // includes
        $compile_includes = new CompilerIncludes;
        $view = $compile_includes->run($view);

        // sections
        $compile_sections = new CompilerSections;
        $view = $compile_sections->run($view);

        // php compiler
        $compile_mcquery = new CompilerPHP;
        $view = $compile_mcquery->run($view);

        // formats
        $view = trim($view);

        // save cache
        $cache_location = $folder_cache . DIRECTORY_SEPARATOR . $template_name;
        $this->template_cache = $cache_location;
        file_put_contents($cache_location, $view);

        createDir(directoryRoot('storage/cache/jsons'));

        $required_files = $compile_includes->files;
        $cache_json_file = directoryRoot('storage/cache/jsons/views.json');

        if (file_exists($cache_json_file)) {
            $cache_data = json_decode(file_get_contents($cache_json_file), true);
            $cache_data[$template_file]['requires'] = $required_files;
            $cache_data[$template_file]['cache'] = $cache_location;
            $cache_data[$template_file]['time'] = filemtime($template_file);
            file_put_contents($cache_json_file, json_encode($cache_data, true));
        } else {
            $new_cache[$template_file]['requires'] = $required_files;
            $new_cache[$template_file]['cache'] = $cache_location;
            $new_cache[$template_file]['time'] = filemtime($template_file);
            file_put_contents($cache_json_file, json_encode($new_cache, true));
        }

        return;
    }

    protected function checkCache()
    {
        $history_cache = directoryRoot('storage/cache/jsons/views.json');

        if (!file_exists($history_cache)) {
            $this->compilerExecute();
        } else {
            $cache = json_decode(file_get_contents($history_cache), true);

            if (!isset($cache[$this->template_file])) {
                $this->compilerExecute();
            } else {
                // checar alteracoes
                $template_time = $cache[$this->template_file]['time'];
                $template_requires = $cache[$this->template_file]['requires'];
                $compiler = false;

                if ($template_time != filemtime($this->template_file)) $compiler = true;  
                if (!file_exists($cache[$this->template_file]['cache'])) $compiler = true;

                if ($template_requires != false and $compiler == false) {
                    foreach ($template_requires as $require => $time) {
                        if ($time != filemtime($require)) {
                            $compiler = true;
                        }
                    }
                }

                if ($compiler == true) {
                    $this->compilerExecute();
                    // dd('alterado');
                } else {
                    // dd('nao alterado');
                    $this->template_cache = $cache[$this->template_file]['cache'];
                }
            }
        }

        return;
    }
}
