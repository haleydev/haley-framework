<?php

namespace Haley\View\Engine;

use Haley\View\Compiler\CompilerPHP;
use Haley\View\Compiler\CompilerSections;
use Exception;
use Haley\View\Compiler\CompilerInclude;

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
        createDir(directoryRoot('storage/cache/views'));
        createDir(directoryRoot('storage/cache/jsons'));

        // cache name
        $cache_name = strtolower(bin2hex(random_bytes(12))) . '.view.php';

        if (file_exists(directoryRoot('storage/cache/views/' . $cache_name))) {
            while (!file_exists(directoryRoot('storage/cache/views/' . $cache_name))) {
                $cache_name = strtolower(bin2hex(random_bytes(12))) . '.view.php';;
            }
        }

        // view file
        $view = file_get_contents($this->view_file);

        // includes
        $compile_include = new CompilerInclude;
        $view = $compile_include->run($view);

        // sections
        $compile_sections = new CompilerSections;
        $view = $compile_sections->run($view);

        // php compiler
        $compile_php = new CompilerPHP;
        $view = $compile_php->run($view);

        // formats
        $view = trim($view);

        // save cache 
        $cache_json_file = directoryRoot('storage/cache/jsons/views.json');
        $this->view_cache = directoryRoot('storage/cache/views/' . $cache_name);

        if (file_exists($cache_json_file)) {
            $cache_data = json_decode(file_get_contents($cache_json_file), true);
            $cache_data[$this->view_file]['cache'] = $this->view_cache;
            $cache_data[$this->view_file]['include'] = $compile_include->include;
            $cache_data[$this->view_file]['filemtime'] = filemtime($this->view_file);
            file_put_contents($cache_json_file, json_encode($cache_data, true));
        } else {
            $new_cache[$this->view_file]['cache'] = $this->view_cache;
            $new_cache[$this->view_file]['include'] = $compile_include->include;
            $new_cache[$this->view_file]['filemtime'] = filemtime($this->view_file);
            file_put_contents($cache_json_file, json_encode($new_cache, true));
        }

        file_put_contents($this->view_cache, $view);

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
                $include = $cache[$this->view_file]['include'];
                $cache_filemtime = $cache[$this->view_file]['filemtime'];
                $atual_filemtime = filemtime($this->view_file);

                $compiler = false;

                if ($cache_filemtime != $atual_filemtime) $compiler = true;

                if ($include != false and $compiler == false) {
                    foreach ($include as $require => $time) {

                        if (file_exists($require)) {
                            if ($time != filemtime($require)) $compiler = true;
                        } else {
                            $compiler = true;
                        }
                    }
                }

                if (!file_exists($cache[$this->view_file]['cache'])) {
                    $compiler = true;
                } elseif ($compiler == true) {
                    unlink($cache[$this->view_file]['cache']);
                }

                if ($compiler == true) {
                    $this->compilerExecute();
                    // echo "alterado";
                } else {
                    // echo "nao alterado";
                    $this->view_cache = $cache[$this->view_file]['cache'];
                }
            }
        }

        return;
    }
}
