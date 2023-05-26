<?php
namespace Haley\Console\Commands;

use Core\Collections\Log;
use Core\Console\Lines;
use Core\Cron;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Command_Cronjob extends Lines
{
    public function cron()
    {
        $check = shell_exec('crontab -l');
        $cron = '* * * * * cd '. ROOT .' && php mcquery cronjob:run >> /dev/null 2>&1';   
        $file = ROOT . "/app/cache/cronjob.txt"; 
        
        if (strtolower(PHP_OS) == 'linux') {
            if (str_contains($check, $cron)) {  
                $new_cron = str_replace($cron,'',$check);
                file_put_contents(ROOT . "/app/cache/cronjob.txt", $new_cron);
                shell_exec('crontab ' . $file);
                shell_exec('sudo service cron restart');    
                
                if(file_exists(ROOT . '/app/cache/cronjob.txt')) {
                    unlink(ROOT . '/app/cache/cronjob.txt');
                }

                $this->red('cron job desativado');
            } else {    
                file_put_contents(ROOT . "/app/cache/cronjob.txt", $cron . $check);
                shell_exec('crontab ' . $file);
                $check = shell_exec('crontab -l');

                if(file_exists(ROOT . '/app/cache/cronjob.txt')) {
                    unlink(ROOT . '/app/cache/cronjob.txt');
                }
                
                if (str_contains($check, $cron)) {
                    // cron job pode pedir senha
                    shell_exec('sudo service cron restart');
                    $this->green('cron job ativado');
                } else {
                    $this->red('erro ao ativar cronjob verifique se o caminho para o mcquery possui pastas com espaços ou caracteres especiais');
                }
            }
        } else {
            $this->red('seu sistema operacional não é linux');
        }
    }

    public function run_jobs()
    {
        $files = scandir(ROOT . "/app/Jobs");
        $scandir = array_diff($files, ['..', '.']);

        // if(!file_exists(ROOT . "/app/Logs/cronjob.log")){
        //     file_put_contents(ROOT . "/app/Logs/cronjob.log", "");
        // }

        dd($files);

        foreach($scandir as $job) {
            $job = str_replace('.php','',$job);
            shell_exec('php '. ROOT .' && php mcquery cronjob:execute '. $job .' >> /dev/null 2>&1');
        }
    }

    public function execute_job(string $job)
    {
        $class = '\app\Jobs\\' . $job;   
        Log::create('cronjob',$class);
        if(file_exists(ROOT . str_replace('\\','/',$class . '.php'))) {
            require_once ROOT . str_replace('\\','/',$class . '.php');
            $schedule = new Cron;
            $execute = new $class;           
            $execute->job($schedule);
            $schedule->execute();
        }
    }

    private function getParams(string $folder)
    {
        $directory_iterator = new RecursiveDirectoryIterator(directoryRoot($folder),FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::CHILD_FIRST);
        $params = [];

        foreach($iterator as $info){ 
            if($info->isFile()) {
                $file = $info->getPathname();
                $class = pathinfo($file, PATHINFO_FILENAME);

                $namespace = trim(str_replace([basename($file),ROOT, '/',$folder], ['','','\\',ucfirst($folder)], $file), '\\') . '\\' . $class;
                $namespace = !empty($namespace) ? $namespace = '\\' . $namespace : '';

                $params[] = [
                    'migration' => $class, 
                    'class' => $namespace
                ];
            }
        } 

        return $params;      
    }
}
