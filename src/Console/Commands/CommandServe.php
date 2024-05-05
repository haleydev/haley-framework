<?php

namespace Haley\Console\Commands;

use Error;
use ErrorException;
use Exception;
use Haley\Console\Lines;
use Haley\Shell\Shell;

class CommandServe
{
    public function run(string|null $port = null)
    {
        if ($port) {
            if ((int)$port != $port or !is_numeric($port)) {
                Lines::red('the port must contain only numbers')->br();

                return;
            } else if (!$this->checkPort($port)) {
                Lines::red('port ' . $port . ' unavailable')->br();

                return;
            }
        } else {
            $port = 3000;

            while ($this->checkPort($port) == false) {

                Lines::red('port ' . $port . ' unavailable')->br();
                $port++;
            }
        }

        Lines::green('development server enabled on')->normal('http://localhost:' . $port)->br()->br();

        $command = sprintf('php -S localhost:%s "%s"', $port, directoryHaley('Collections/Server.php'));

        Shell::exec($command, function ($line) {
            if (isJson($line)) {
                $data = json_decode($line, true);

                if (!empty($data['file'])) {
                    $start = Shell::normal($data['date'], true, false);
                    $start .= Shell::gray(strtoupper(formatSize($data['file']['size'])), false, false);

                    $end = Shell::magenta('FILE', true, false);
                    $end .= Shell::blue($data['file']['url'], false, false);

                    Shell::list($start, $end)->br();
                } elseif (!empty($data['request'])) {
                    $start = Shell::normal($data['date'], true, false);
                    $start .= Shell::gray(strtoupper(formatSize($data['request']['size'] ?? 0)), false, false);

                    $end = Shell::green($data['request']['method'], true, false);
                    $end .= Shell::blue($data['request']['url'], false, false);

                    Shell::list($start, $end)->br();
                }
            }

            // if (!str_contains($line, 'Development Server')) Lines::normal($line)->br();
        }, 'server', 'development server port ' . $port);
    }

    private function checkPort(string $port)
    {
        try {
            if ($socket = @fsockopen('localhost', $port, $errno, $errstr, 2)) {
                return false;
                fclose($socket);
            } else {
                return true;
            }
        } catch (ErrorException) {
            return true;
        } catch (Error) {
            return true;
        } catch (Exception) {
            return true;
        }
    }
}
