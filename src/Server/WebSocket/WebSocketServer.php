<?php

namespace Haley\Server\WebSocket;

use Haley\Shell\Shell;
use Swoole\WebSocket\Server;
use Swoole\Timer;
use Throwable;

class WebSocketServer
{
    protected Server|null $server = null;

    public function run(array $params)
    {
        try {
            $this->server = new Server($params['host'], $params['port']);

            $class = $params['class'];

            if (!class_exists($class) and $params['namespace']) $class = $params['namespace'] . '\\' . $class;

            if (!class_exists($class)) {
                Shell::red('Class not found: ' . $class)->br();
                die;
            }

            $class = new $class();
        } catch (Throwable $error) {
            Shell::red($error->getMessage())->br();
            die;
        }

        $this->server->on('handshake', function ($request, $response) use ($params, $class) {
            $status = $this->server->stats();

            if (!empty($params['connections'])) {
                if ($status['connection_num'] > $params['connections']) {
                    $response->end();
                    return false;
                }
            }

            $request_params = [];

            if (!empty($params['path'])) {
                $request_params = $this->handlePath($params['path'], $request->server['path_info']);

                if ($request_params === false) {
                    $response->end();
                    return false;
                };
            }

            $secWebSocketKey = $request->header['sec-websocket-key'];
            $pattern = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';

            if (0 === preg_match($pattern, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
                $response->end();
                return false;
            }

            $key = base64_encode(
                sha1(
                    $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                    true
                )
            );

            $headers = [
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Accept' => $key,
                'Sec-WebSocket-Version' => '13',
            ];

            if (isset($request->header['sec-websocket-protocol'])) {
                $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
            }

            foreach ($headers as $key => $val) $response->header($key, $val);

            $response->status(101);
            $response->end();

            if (!method_exists($class, 'onOpen')) return;

            try {
                $class->onOpen($request->fd, $request_params, $request->header, new WebSocket($request->fd, $this->server));
            } catch (Throwable $error) {
                $this->handleError($request->fd, $class, 'open', $error);
            }
        });

        $this->server->on('message', function ($server, $frame) use ($class, $params) {
            if (!method_exists($class, 'onMessage') or empty($params['receive'])) return;

            // dd($frame);
            // frame upcode

            try {
                $class->onMessage($frame->fd, $frame->data, new WebSocket($frame->fd, $server));
            } catch (Throwable $error) {
                $this->handleError($frame->fd, $class, 'message', $error);
            }
        });

        $this->server->on('close', function ($server, $fd) use ($class) {
            if (!method_exists($class, 'onClose')) return;

            try {
                $class->onClose($fd, new WebSocket($fd, $server));
            } catch (Throwable $error) {
                $this->handleError($fd, $class, 'close', $error);
            }
        });

        // Timer::tick(5000, function () {
        //     dd($this->server->stats());
        // });

        $this->server->start();

        die;
    }

    protected function handleError(int $fd, $class, string $on, Throwable $error)
    {
        if (!method_exists($class, 'onClose')) return;

        $class->onError($on, $error, new WebSocket($fd, $this->server));
    }

    protected function handlePath(string $path, string $request_path)
    {
        $path = trim($path, '/');
        $request_path = trim($request_path, '/');
        $check = $path;
        $params = [];

        if (preg_match('/{(.*?)}/', $path)) {
            $array_route = explode('/', $path);
            $array_url = explode('/', $request_path);

            foreach ($array_route as $key => $value) {
                if (preg_match('/{(.*?)}/', $value, $math)) {
                    $param = str_replace(['?}', '{', '}'], '', $math[0]);

                    if (isset($array_url[$key])) {
                        $params[$param] = $array_url[$key];
                        $check = str_replace($math[0], $array_url[$key], $check);
                    } elseif (substr($value, -2) == '?}') {
                        $params[$param] = null;
                        $check = str_replace("/$math[0]", '', $check);
                    }
                }
            }
        }

        if ($check == $request_path) return $params;

        return false;
    }
}
