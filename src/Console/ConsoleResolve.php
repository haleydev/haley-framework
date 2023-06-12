<?php

namespace Haley\Console;

use Error;
use Exception;
use InvalidArgumentException;
use PDOException;
use ReflectionFunction;
use ReflectionMethod;
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

        $callback = null;

        try {
            if (is_string($command['action'])) {
                if (str_contains($command['action'], '::')) {
                    $params = explode('::', $command['action']);
                } else if (str_contains($command['action'], '@')) {
                    $params = explode('@', $command['action']);
                }

                $namespace = !empty($command['namespace']) ? $command['namespace'] . '\\' : '';

                if (isset($params[0]) and isset($params[1])) {
                    $command['action'] = [];
                    $class = $namespace . $params[0];
                    $command['action'][0] = new $class;
                    $command['action'][1] = $params[1];
                    $reflection = new ReflectionMethod($command['action'][0], $command['action'][1]);
                    $callback = $command['action'];
                }
            } elseif (is_array($command['action'])) {
                $command['action'][0] = new $command['action'][0];
                $reflection = new ReflectionMethod($command['action'][0], $command['action'][1]);
                $callback = $command['action'];
            } elseif (is_callable($command['action'])) {
                $callback = $command['action'];
                $reflection = new ReflectionFunction($callback);
            }

            if (is_callable($callback)) {
                $parameters = $reflection->getParameters();
                $args = [];

                foreach ($parameters as $value) {
                    $arg = $value->getName();
                    if (array_key_exists($arg, $this->params)) {
                        $args[$arg] = $this->params[$arg];
                    }
                }

                call_user_func_array($callback, $args);
            }
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
