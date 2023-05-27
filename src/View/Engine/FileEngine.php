<?php

namespace Haley\View\Engine;

use Haley\View\Compiler\CompilerIncludes;
use Haley\View\Compiler\CompilerPHP;
use Haley\View\Compiler\CompilerSections;
use Exception;

class FileEngine
{
    private false|string $view_file = false;
    private false|string $view_cache = false;

    /**
     * @return false|string
     */
    public function getView(string $file)
    {
        if (!file_exists($file)) throw new Exception('File not found: ' . $file);        

        $this->view_file = $file;

        $this->checkCache();     

        return $this->view_cache;
    }   

    protected function compilerExecute()
    {
        $view_file = $this->view_file;
        $template_name = basename($view_file);
        $folder_cache = directoryRoot('storage/cache/views' . rtrim(str_replace([directoryRoot('resources/views'), $template_name], '', $view_file), DIRECTORY_SEPARATOR));

        createDir($folder_cache);

        // view file
        $view = file_get_contents($this->view_file);

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
        $this->view_cache = $cache_location;
        file_put_contents($cache_location, $view);

        createDir(directoryRoot('storage/cache/jsons'));

        $required_files = $compile_includes->files;
        $cache_json_file = directoryRoot('storage/cache/jsons/views.json');

        if (file_exists($cache_json_file)) {
            $cache_data = json_decode(file_get_contents($cache_json_file), true);
            $cache_data[$view_file]['requires'] = $required_files;
            $cache_data[$view_file]['cache'] = $cache_location;
            $cache_data[$view_file]['time'] = filemtime($view_file);
            file_put_contents($cache_json_file, json_encode($cache_data, true));
        } else {
            $new_cache[$view_file]['requires'] = $required_files;
            $new_cache[$view_file]['cache'] = $cache_location;
            $new_cache[$view_file]['time'] = filemtime($view_file);
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

            if (!isset($cache[$this->view_file])) {
                $this->compilerExecute();
            } else {
                // checar alteracoes
                $template_time = $cache[$this->view_file]['time'];
                $template_requires = $cache[$this->view_file]['requires'];
                $compiler = false;

                if ($template_time != filemtime($this->view_file)) $compiler = true;  
                if (!file_exists($cache[$this->view_file]['cache'])) $compiler = true;

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
                    $this->view_cache = $cache[$this->view_file]['cache'];
                }
            }
        }

        return;
    }
}
