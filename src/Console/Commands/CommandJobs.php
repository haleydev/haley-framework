<?php

namespace Haley\Console\Commands;

use Haley\Collections\Log;
use Haley\Console\Lines;
use Haley\Jobs\JobMemory;
use Haley\Collections\Config;
use Haley\Shell\Shell;
use Throwable;

class CommandJobs extends Lines
{
    private array $cache = [
        'master' => [
            'pid' => null,
            'active' => false
        ],

        'jobs' => []
    ];

    // start (verifica timeouts e arrays vazios) => executa
    // list para listar jobs e seus status

    public function active()
    {
        if (self::running()) {
            $this->stopJobs();

            Shell::red('jobs disabled')->br();
        } else {
            Shell::green('jobs enabled')->br();
            $this->startJobs();
        }
    }

    public function stopJobs()
    {
        $cache = $this->cache;

        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        if ($cache['master']['active']) Shell::kill($cache['master']['pid']);

        $pids = [];

        foreach ($cache['jobs'] as $job) {
            foreach ($job as $value) $pids[] = $value['pid'];
        }

        file_put_contents($cache_path, json_encode($this->cache));

        Shell::kill($pids);
    }

    public function startJobs()
    {
        set_time_limit(0);

        $this->stopJobs(false);

        $pid = getmypid();

        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');
        $cache = $this->cache;

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        $cache['master'] = [
            'active' => true,
            'pid' => $pid
        ];

        file_put_contents($cache_path, json_encode($cache));

        $minute = date('i');

        while (true) {
            if ($minute == date('i')) {
                sleep(1);

                continue;
            }

            $minute = date('i');

            // adicionar forma de ler os jobs aqui e adicionar as chaves no json para uma outra funcao executar

            Shell::exec('cd ' . directoryRoot() . ' && php haley job:run >> /dev/null 2>&1');
        }
    }

    public function run(string|null $name = null)
    {
        foreach (Config::route('job', []) as $job) require_once $job;

        $this->yellow('running jobs...')->br();

        createDir(directoryRoot('storage/cache/jsons'));

        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');
        $cache = $this->cache;
        $run = false;

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        // kill jobs timeout
        foreach ($cache['jobs'] as $job_key => $job) {
            if (empty($job)) {

                unset($cache['jobs'][$job_key]);
                continue;
            }

            foreach ($job as $key => $value) {
                if ($value['timeout']) {
                    if (strtotime('now') >= $value['timeout']) {
                        if (Shell::kill($value['pid'])) {
                            $job_name = '???';
                            $job_description = '???';

                            if (array_key_exists($job_key, JobMemory::$jobs)) {
                                if (JobMemory::$jobs[$job_key]['name']) $job_name = JobMemory::$jobs[$job_key]['name'];
                                if (JobMemory::$jobs[$job_key]['description']) $job_name = JobMemory::$jobs[$job_key]['description'];
                            }

                            Log::create('jobs', sprintf('TIMEOUT KILL - %s : %s', $job_name, $job_description));
                        }

                        unset($cache['jobs'][$job_key][$key]);
                    }
                }
            }

            if (!array_key_exists($job_key, JobMemory::$jobs)) unset($cache['jobs'][$job_key]);
        }

        foreach (JobMemory::$jobs as $key => $job) {
            if ($name !== null and $job['name'] !== $name) continue;

            if (array_key_exists($key, $cache['jobs'])) {
                foreach ($cache['jobs'][$key] as $cache_key => $cache_value) {
                    $posix_getpgid = posix_getpgid($cache_value['pid']);

                    if ($job['unique'] and $posix_getpgid) $job['valid'] = false;
                    if (!$posix_getpgid) unset($cache['jobs'][$key][$cache_key]);
                }
            }

            if ($job['valid'] == true) {
                $run = true;
                $log = 'STARTED';

                if (!empty($job['name'])) $log .= ' - ' . $job['name'];
                if (!empty($job['description'])) $log .= ' : ' . $job['description'];

                Log::create('jobs', $log);

                $mesage = sprintf('%s - %s', $job['name'] ?? '???', $job['description'] ?? '???');

                $this->green('executed')->normal($mesage)->br();

                shell_exec('php ' . directoryRoot() . ' && php haley job:execute ' . $key . ' > /dev/null 2>&1 &');
            }
        }

        file_put_contents($cache_path, json_encode($cache, true));

        if ($name !== null and $run == false) $this->red('job ' . $name . ' not found')->br();

        $run ? $this->yellow('finished jobs')->br() : $this->red('no job to be done')->br();
    }

    public function execute(string $key)
    {
        set_time_limit(0);

        foreach (Config::route('job') as $job) require_once $job;

        $pid = getmypid();
        $cache = $this->cache;
        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        if (array_key_exists($key, JobMemory::$jobs)) {
            $job = JobMemory::$jobs[$key];
            $log = 'FINISHED';
            $log_error = null;
            $action = $job['action'] ?? null;

            if (!empty($action)) {
                try {
                    $timeout = $job['timeout'] ? strtotime('+' . $job['timeout'] . ' minutes') : null;

                    $cache['jobs'][$key][] = [
                        'pid' => $pid,
                        'timeout' => $timeout
                    ];

                    file_put_contents($cache_path, json_encode($cache, true));

                    // execute
                    executeCallable($action, [], $job['namespace']);
                } catch (Throwable $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                }
            }

            if (!empty($log_error)) $log = 'ERROR';
            if (!empty($job['name'])) $log .= ' - ' . $job['name'];
            if (!empty($job['description'])) $log .= ' : ' . $job['description'];
            if (!empty($log_error)) $log .= ' -> ' . $log_error;

            Log::create('jobs', $log);

            $cache = json_decode(file_get_contents($cache_path), true);

            if (array_key_exists($key, $cache['jobs'])) {
                foreach ($cache['jobs'][$key] as $cache_key => $value) {
                    if ($value['pid'] !== $pid) continue;

                    unset($cache['jobs'][$key][$cache_key]);
                    file_put_contents($cache_path, json_encode($cache, true));
                }
            }
        }
    }

    public function running()
    {
        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');

        if (!file_exists($cache_path)) return false;

        $cache = json_decode(file_get_contents($cache_path), true);

        if ($cache['master']['active']) {
            if (Shell::running($cache['master']['pid'])) return true;
        }

        return false;
    }
}
