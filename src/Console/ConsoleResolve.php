<?php

namespace Haley\Console;

use Error;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PDOException;
use UnderflowException;

class ConsoleResolve
{
    private string $console = '';
    private array $params = [];

    public function run(array $commands)
    {
        global $argv;
        unset($argv[0]);
        $this->console = preg_replace('/( ){2,}/', '$1', implode(' ', $argv));

        foreach ($commands as $value) {
            if ($this->checkCommand($value['command'])) {
                return $this->execute($value);
            }
        }

        Lines::red('command not found')->br();

        exit;
    }

    private function execute(array $command)
    {
        if (empty($command['action'])) return;

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        try {
            executeCallable($command['action'], $this->params, $command['namespace'] ?? null);
        } catch (PDOException $error) {
            Lines::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();
        } catch (Error $error) {
            Lines::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();
        } catch (UnderflowException $error) {
            Lines::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();
        } catch (InvalidArgumentException $error) {
            Lines::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();
        } catch (Exception $error) {
            Lines::red("{$error->getMessage()} : {$error->getFile()} {$error->getLine()}")->br();
        }
    }

    private function checkCommand(string $command)
    {
        $check = $command;

        $params = [];

        if (preg_match('/{(.*?)}/', $command)) {
            $array_command = explode(' ', $command);
            $array_console = explode(' ', $this->console);

            foreach ($array_command as $key => $value) {
                if (preg_match('/{(.*?)}/', $value, $math)) {
                    $param = str_replace(['?}', '{', '}'], '', $math[0]);

                    if (isset($array_console[$key])) {
                        $params[$param] = $array_console[$key];
                        $check = str_replace($math[0], $array_console[$key], $check);
                    } elseif (substr($value, -2) == '?}') {
                        $params[$param] = null;
                        $check = str_replace("$math[0]", '', $check);
                    }
                }
            }
        }

        $check = trim($check);

        if ($this->console != $check) return false;

        $this->params = $params;
        return true;
    }
}
