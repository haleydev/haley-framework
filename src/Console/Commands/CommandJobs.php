<?php

namespace Haley\Console\Commands;

use Haley\Collections\Log;
use Haley\Console\Lines;
use Haley\Jobs\JobMemory;
use Haley\Collections\Config;
use Throwable;

class CommandJobs extends Lines
{
    public function active()
    {
        $check = shell_exec('crontab -l') ?? '';
        $cron = '* * * * * cd ' . directoryRoot() . ' && php haley job:run >> /dev/null 2>&1' . PHP_EOL;
        $file = directoryRoot('storage/cache/cronjob.txt');

        if (strtolower(PHP_OS) == 'linux') {
            if (str_contains($check, $cron)) {
                $new_cron = str_replace($cron, '', $check);
                file_put_contents($file, $new_cron);
                shell_exec('crontab ' . $file);
                shell_exec('sudo service cron restart');

                if (file_exists($file)) unlink($file);

                $this->red('jobs disabled')->br();
            } else {
                file_put_contents($file, $cron . $check);
                shell_exec('crontab ' . $file);
                $check = shell_exec('crontab -l') ?? '';

                if (file_exists($file)) unlink($file);

                if (str_contains($check, $cron)) {
                    // cron job pode pedir senha
                    shell_exec('sudo service cron restart');
                    $this->green('jobs enabled')->br();
                } else {
                    $this->red('failure to activate jobs')->br();
                }
            }
        } else {
            $this->red('your operating system is not linux')->br();
        }
    }

    public function run(string|null $name = null)
    {
        foreach (Config::route('job', []) as $job) require_once $job;

        $this->yellow('running jobs...')->br();

        createDir(directoryRoot('storage/cache/jsons'));

        $cache_path = directoryRoot('storage/cache/jsons/jobs.json');
        $cache = [];
        $run = false;

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        // kill jobs timeout
        foreach ($cache as $cache_x_key => $cache_x_value) {
            if (empty($cache_x_value)) continue;

            foreach ($cache_x_value as $cache_key => $cache_value) {
                if ($cache_value['timeout']) {
                    if (strtotime('now') >= $cache_value['timeout']) {
                        if (posix_kill($cache_value['pid'], SIGTERM)) {
                            $job_name = '???';
                            $job_description = '???';

                            if (array_key_exists($cache_x_key, JobMemory::$jobs)) {
                                if (JobMemory::$jobs[$cache_x_key]['name']) $job_name = JobMemory::$jobs[$cache_x_key]['name'];
                                if (JobMemory::$jobs[$cache_x_key]['description']) $job_name = JobMemory::$jobs[$cache_x_key]['description'];
                            }

                            Log::create('jobs', sprintf('TIMEOUT KILL - %s : %s', $job_name, $job_description));
                        }

                        unset($cache[$cache_x_key][$cache_key]);
                    }
                }
            }

            if (!array_key_exists($cache_x_key, JobMemory::$jobs)) unset($cache[$cache_x_key]);
        }

        foreach (JobMemory::$jobs as $key => $job) {
            if ($name !== null and $job['name'] !== $name) continue;

            if (array_key_exists($key, $cache)) {
                foreach ($cache[$key] as $cache_key => $cache_value) {
                    $posix_getpgid = posix_getpgid($cache_value['pid']);

                    if ($job['unique'] and $posix_getpgid) $job['valid'] = false;
                    if (!$posix_getpgid) unset($cache[$key][$cache_key]);
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
        $pid = getmypid();
        foreach (Config::route('job') as $job) require_once $job;

        if (array_key_exists($key, JobMemory::$jobs)) {
            $job = JobMemory::$jobs[$key];
            $log = 'FINISHED';
            $log_error = null;
            $action = $job['action'] ?? null;

            // execute
            if (!empty($action)) {             

                try {
                    $timeout = $job['timeout'] ? strtotime('+' . $job['timeout'] . ' minutes') : null;
                    $cache_path = directoryRoot('storage/cache/jsons/jobs.json');

                    if (file_exists($cache_path)) {
                        $cache = json_decode(file_get_contents($cache_path), true);
                    } else {
                        $cache = [];
                    }

                    $cache[$key][] = [
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

            if (array_key_exists($key, $cache)) {
                foreach ($cache[$key] as $cache_key => $value) {
                    if ($value['pid'] !== $pid) continue;

                    unset($cache[$key][$cache_key]);
                    file_put_contents($cache_path, json_encode($cache, true));
                }
            }

            // posix_kill($pid, SIGTERM);
        }
    }
}
