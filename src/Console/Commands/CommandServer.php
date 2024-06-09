<?php

namespace Haley\Console\Commands;

use Haley\Collections\Config;
use Haley\Server\ServerMemory;
use Haley\Server\WebSocket\WebSocketServer;
use Haley\Shell\Shell;
use Throwable;

class CommandServer
{
    public function start(string|null $name = null)
    {
        if (!extension_loaded('swoole')) {
            Shell::red('swoole extension not found')->br();

            return;
        }

        if (!is_writable(directoryRoot('storage/cache/jsons'))) {
            Shell::red('storage/cache/jsons not writable')->br();

            return;
        }

        $config = Config::route('server');

        if (empty($config['path'])) {
            Shell::red('config path not found')->br();

            return;
        }

        $cache_path = directoryRoot('storage/cache/jsons/server.json');
        $cache = [];

        if (file_exists($cache_path)) {
            if (!is_readable($cache_path)) {
                Shell::red('storage/cache/jsons/server.json not readable')->br();

                return;
            }

            $cache = json_decode(file_get_contents($cache_path), true);
        }

        $count = 0;

        try {
            foreach ($config['path'] as $path) require_once $path;

            foreach (ServerMemory::$servers as $type => $servers) {
                foreach ($servers as $params) {
                    if (!empty($name) and $name != $params['name']) continue;

                    $count++;

                    if (empty($params['host'])) $params['host'] = $config['host'] ?? 'localhost';
                    if (empty($params['alias'])) $params['alias'] = $config['alias'] ?? 'localhost';

                    $data = [
                        'type' => $type,
                        'port' => $params['port'],
                        'host' => $params['host'],
                        'name' => $params['name'],
                        'alias' => $params['alias'],
                    ];

                    $pid = Shell::exec('php ' . directoryRoot() . ' && php haley server:run ' . $type . ':' .  $params['host'] . ':' . $params['port'] . ' > /dev/null 2>&1 &');

                    $start = Shell::normal($type, true, false);
                    $start .= Shell::gray($params['alias'] . ':' . $params['port'], false, false);

                    if ($pid !== true and $pid !== false) {
                        usleep(500000);

                        if (Shell::running($pid)) {
                            if (!array_key_exists($type, $cache)) $cache[$type] = [];
                            $cache[$type][$pid] = $data;
                            $end = Shell::green('SUCCESS', true, false);
                        } else {
                            $end = Shell::red('FAILED', true, false);
                        }
                    } else {
                        $end = Shell::red('FAILED', true, false);
                    }

                    Shell::list($start, $end)->br();

                    if (!empty($name)) break;
                }
            }

            if (!$count) {
                if (!$name) Shell::red('no server found')->br();
                else Shell::red('server ' . $name . ' not found')->br();
            }

            file_put_contents($cache_path, json_encode($cache));
        } catch (Throwable $th) {
            Shell::red($th->getMessage())->br();
        }
    }

    public function stop(string|null $name = null)
    {
        if (!is_writable(directoryRoot('storage/cache/jsons'))) {
            Shell::red('storage/cache/jsons not writable')->br();

            return;
        }

        $cache_path = directoryRoot('storage/cache/jsons/server.json');

        if (!file_exists($cache_path)) return;

        if (!is_readable($cache_path)) {
            Shell::red('storage/cache/jsons/server.json not readable')->br();

            return;
        }

        $cache = json_decode(file_get_contents($cache_path), true);
        $count = 0;

        foreach ($cache as $type => $servers) {
            foreach ($servers as $pid => $params) {
                $exist = Shell::running($pid);
                $kill = false;

                if ($name === null) {
                    $kill = Shell::kill($pid);
                    unset($cache[$type][$pid]);
                } else {
                    if ($name === $params['name']) {
                        $kill = Shell::kill($pid);
                        unset($cache[$type][$pid]);
                    }
                }

                if ($kill and $exist) {
                    $count++;
                    $start = Shell::normal($type, true, false);
                    $start .= Shell::gray($params['alias'] . ':' . $params['port'], false, false);
                    $end = Shell::red('STOPPED', true, false);

                    Shell::list($start, $end)->br();
                }
            }
        }

        if (!$count) {
            if (!$name) Shell::red('no server running')->br();
            else Shell::red('server ' . $name . ' not running')->br();
        }

        file_put_contents($cache_path, json_encode($cache));
    }

    public function run(string|null $name = null)
    {
        if (!extension_loaded('swoole')) {
            Shell::red('swoole extension not found')->br();

            return;
        }

        if (!is_writable(directoryRoot('storage/cache/jsons'))) {
            Shell::red('storage/cache/jsons not writable')->br();

            return;
        }

        $config = Config::route('server');

        if (empty($config['path'])) {
            Shell::red('config path not found')->br();

            return;
        }

        $check_call = explode(':', $name);
        $call = null;

        if (count($check_call) === 3) $call = [
            'type' => $check_call[0],
            'host' => $check_call[1],
            'port' => $check_call[2]
        ];

        try {
            foreach ($config['path'] as $path) require_once $path;

            foreach (ServerMemory::$servers as $type => $servers) {
                foreach ($servers as $params) {
                    if (empty($params['host'])) $params['host'] = $config['host'] ?? 'localhost';
                    if (empty($params['alias'])) $params['alias'] = $config['alias'] ?? 'localhost';

                    if (is_array($call)) {
                        if ($type == $call['type'] and $params['host'] == $call['host'] and $params['port'] == $call['port']) {
                            $this->execute($type, $params);
                        }
                    } elseif ($name == $params['name']) {
                        $this->execute($type, $params);
                    }
                }
            }
        } catch (Throwable $th) {
            Shell::red($th->getMessage())->br();
        }
    }

    public function list()
    {
        if (!is_writable(directoryRoot('storage/cache/jsons'))) {
            Shell::red('storage/cache/jsons not writable')->br();

            return;
        }

        $config = Config::route('server');

        if (empty($config['path'])) {
            Shell::red('config path not found')->br();

            return;
        }

        $cache_path = directoryRoot('storage/cache/jsons/server.json');
        $cache = [];

        if (file_exists($cache_path)) {
            if (!is_readable($cache_path)) {
                Shell::red('storage/cache/jsons/server.json not readable')->br();

                return;
            }

            $cache = json_decode(file_get_contents($cache_path), true);
        }

        $online = [];

        foreach ($cache as $type => $servers) {
            foreach ($servers as $pid => $params) {
                if (Shell::running($pid)) {
                    $online[] = $type . ':' . $params['host'] . ':' . $params['port'];
                } else {
                    unset($cache[$type][$pid]);
                }
            }
        }

        foreach ($config['path'] as $path) require_once $path;

        foreach (ServerMemory::$servers as $type => $servers) {
            foreach ($servers as $params) {
                if (empty($params['host'])) $params['host'] = $config['host'] ?? 'localhost';
                if (empty($params['alias'])) $params['alias'] = $config['alias'] ?? 'localhost';

                $start = Shell::normal($type, true, false);
                $start .= Shell::gray($params['alias'] . ':' . $params['port'], false, false);

                if (in_array($type . ':' . $params['host'] . ':' . $params['port'], $online)) {
                    $end = Shell::green('ONLINE', true, false);
                } else {
                    $end = Shell::red('OFFLINE', true, false);
                }

                Shell::list($start, $end)->br();
            }
        }

        file_put_contents($cache_path, json_encode($cache));
    }

    protected function execute(string $type, array $params)
    {
        if ($type == 'websocket') (new WebSocketServer)->run($params);
    }
}
