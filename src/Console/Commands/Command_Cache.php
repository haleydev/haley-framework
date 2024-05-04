<?php
namespace Haley\Console\Commands;
use Haley\Console\Lines;

class Command_Cache extends Lines
{
    public function cache_env()
    {
        $json = directoryRoot('storage/cache/jsons/env.json');

        createDir('storage/cache/jsons');

        if (file_exists($json)) {
            unlink($json);
            $this->red('cache env desativado');
        } else {
            if (file_exists($json)) unlink($json);

            $env = env();

            $file = array_filter($env);

            file_put_contents($json,json_encode($file,true));


            if(file_exists($json)){
                $this->green('cache env ativado');
            }else{
                $this->red('erro ao gravar cache do .env');
            }
        }
    }

    public function template_clear()
    {
        $files = directoryRoot('storage/cache/views');
        $json = directoryRoot('storage/cache/jsons/views.json');

        deleteDir($files);
        deleteFile($json);

        if (!file_exists($files) and !file_exists($json)){
            $this->green('cache de views limpo');
        }else{
            $this->red('falha ao limpar cache (verifique as permissões)');
        }
    }
}