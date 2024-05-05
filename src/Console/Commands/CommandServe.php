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
            if (!str_contains($line, 'Development Server')) Lines::normal($line)->br();
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
