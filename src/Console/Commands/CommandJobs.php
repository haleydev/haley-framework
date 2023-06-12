<?php

namespace Haley\Console\Commands;

use Haley\Collections\Log;
use Haley\Console\Lines;
use Haley\Jobs\JobMemory;
use Error;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PDOException;
use UnderflowException;

class CommandJobs
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

                Lines::red('jobs disabled')->br();
            } else {
                file_put_contents($file, $cron . $check);
                shell_exec('crontab ' . $file);
                $check = shell_exec('crontab -l') ?? '';

                if (file_exists($file)) unlink($file);

                if (str_contains($check, $cron)) {
                    // cron job pode pedir senha
                    shell_exec('sudo service cron restart');
                    Lines::green('jobs enabled')->br();
                } else {
                    Lines::red('failure to activate jobs')->br();
                }
            }
        } else {
            Lines::red('your operating system is not linux')->br();
        }
    }

    public function run(string|null $name = null)
    {
        require_once directoryRoot('routes/job.php');

        $especific = false;

        foreach (JobMemory::$jobs as $key => $job) {
            if (!empty($name)) {
                if ($job['name'] != $name) continue;
                else {
                    $especific = true;
                    Lines::green('job ' . $name . ' executed')->br();
                };
            }

            if ($job['valid'] == true) {
                shell_exec('php ' . directoryRoot() . ' && php haley job:execute ' . $key . ' > /dev/null 2>&1 &');

                $log = 'STARTED';

                if (!empty($job['name'])) $log .= ' - ' . $job['name'];
                if (!empty($job['description'])) $log .= ' : ' . $job['description'];

                Log::create('jobs', $log);
            }
        };

        if (!empty($name) and $especific == false) {
            Lines::red('job ' . $name . ' not found')->br();
        }
    }

    public function execute(string $key)
    {
        require_once directoryRoot('routes/job.php');

        if (array_key_exists($key, JobMemory::$jobs)) {
            $job = JobMemory::$jobs[$key];
            $log = 'FINISHED';
            $log_error = null;
            $action = $job['action'] ?? null;

            if (!empty($action)) {
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
                });

                try {
                    if (is_string($action)) {
                        $params = explode('::', $action);
                        $namespace = !empty($job['namespace']) ? $job['namespace'] . '\\' : '';

                        if (isset($params[0]) and isset($params[1])) {
                            $class = $namespace . $params[0];
                            $method = $params[1];
                            $rum = new $class;
                            $rum->$method();
                        }
                    } elseif (is_array($action)) {
                        $action[0] = new $action[0]();
                        if (is_callable($action)) call_user_func($action);
                    } elseif (is_callable($action)) {
                        call_user_func($action);
                    }
                } catch (PDOException $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                } catch (Error $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                } catch (UnderflowException $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                } catch (InvalidArgumentException $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                } catch (Exception $error) {
                    $log_error = "{$error->getMessage()} : {$error->getFile()} {$error->getLine()}";
                }
            }

            if (!empty($log_error)) $log = 'ERROR';
            if (!empty($job['name'])) $log .= ' - ' . $job['name'];
            if (!empty($job['description'])) $log .= ' : ' . $job['description'];
            if (!empty($log_error)) $log .= ' -> ' . $log_error;

            Log::create('jobs', $log);
        }
    }
}
