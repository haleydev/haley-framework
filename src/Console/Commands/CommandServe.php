<?php

namespace Haley\Console\Commands;

use Error;
use ErrorException;
use Exception;
use Haley\Console\Lines;

class CommandServe
{
    public function run(string|null $port = null)
    {
        if (empty($port)) {
            $port = 3000;

            while ($this->checkPort($port) == false) {
                Lines::red('port ' . $port . ' unavailable')->br();
                $port++;
            }

            Lines::green('development server enabled on')->normal('http://localhost:' . $port)->br();

            shell_exec('php -S localhost:' . $port . ' ' . directoryHaley('Collections/Server.php'));
        } else  if (is_numeric($port)) {
            if ($this->checkPort($port)) {
                Lines::green('development server enabled on')->normal('http://localhost:' . $port)->br();
                shell_exec('php -S localhost:' . $port . ' ' . directoryHaley('Collections/Server.php'));
            } else {
                Lines::red('port ' . $port . ' unavailable')->br();
            }
        } else {
            Lines::red('the port must contain only numbers')->br();
        }
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
