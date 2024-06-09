<?php

namespace Haley\Server;

use Swoole\Timer as SwooleTimer;

class Timer
{
    public function setInterval(int $milliseconds, callable $callback)
    {
        SwooleTimer::tick($milliseconds, $callback);
    }

    public function setTimeout(int $milliseconds, callable $callback)
    {
        return SwooleTimer::after($milliseconds, $callback);
    }

    public function clear(int $id)
    {
        return SwooleTimer::clear($id);
    }

    public function clearAll()
    {
        return SwooleTimer::clearAll();
    }

    public function info(int $id)
    {
        return SwooleTimer::info($id);
    }
}
