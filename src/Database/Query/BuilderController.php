<?php

namespace Haley\Database\Query;

use Haley\Collections\Log;
use Haley\Database\Query\Grammars\GrammarsProcessor;
use InvalidArgumentException;

class BuilderController
{
    protected string $query;
    protected array $bindparams = [];
    protected array $params = [];

    protected function add(string $action, mixed $params, bool $array = true)
    {
        if ($array == true) {
            $this->params[$action][] = $params;
        } else {
            $this->params[$action] = $params;
        }
    }

    protected function keyLast(string $action)
    {
        if (!array_key_exists($action, $this->params)) return null;

        return array_key_last($this->params[$action]);
    }

    protected function executeProcessor(string $command, string $driver)
    {
        if (!in_array($driver, ['mysql'])) {
            Log::create('connection', "Drive not found! ( {$driver} )");
            throw new InvalidArgumentException("Drive not found! ( {$driver} )");
        }

        $syntax = new GrammarsProcessor;
        $syntax->params = $this->params;

        $this->query = $syntax->query($command, $driver);
        $this->bindparams = $syntax->bindparams;
    }
}
