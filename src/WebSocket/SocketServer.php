<?php

namespace Haley\WebSocket;

use Haley\Console\Lines;

use Throwable;

class SocketServer
{
    private object|null $class = null;
    private object|null $ws = null;

    public function run(array $definitions)
    {
        $this->ws = new Swoole\WebSocket\Server($definitions['host'], $definitions['port']);
        $this->class = executeCallable($definitions['action']);

        var_dump($definitions);

        $this->ws->on('Open', function ($ws, $request) {
            SocketMemory::$clients[$request->fd] = [];
               SocketMemory::$client_id = $request->fd;

            if (!method_exists($this->class, 'onOpen')) return;

            $this->onOpen(new SocketClient);

            $this->actions();

            // $this->ws->push($request->fd, "hello, welcome\n");
        });


        $this->ws->on('Message', function ($ws, $frame) {
            if (!method_exists($this->class, 'onMessage')) return;


            // if ($frame->opcode == WEBSOCKET_OPCODE_BINARY) {
            //     // Trate o blob recebido
            //     $blobData = $frame->data;

            //     // Envie o blob de volta para o cliente

            //     // foreach ($this->server->connections as $fd)

            //     // foreach (Clients::$clients as $fd => $value) $this->ws->push($fd, $blobData, WEBSOCKET_OPCODE_BINARY);
            // } else {
            //     // Caso não seja um blob, trate conforme necessário
            //     $this->ws->push($frame->fd, "Apenas blobs são suportados neste exemplo.");
            // }


            $this->class->onMessage($frame->data, $frame->opcode , new SocketClient);


            $this->actions();
        });

        $this->ws->on('Close', function ($ws, $fd) {
            if (array_key_exists($fd, SocketMemory::$clients)) unset(SocketMemory::$clients[$fd]);


            if (!method_exists($this->class, 'onClose')) return;
        });

        $this->ws->start();
    }

    private function actions()
    {
        foreach (SocketMemory::$close as $fd) $this->ws->disconnect($fd);

        if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
            if (count($value['id'])) foreach ($value['id'] as $to) {

                $this->ws->push($to,$value['data']);
                // if (array_key_exists($to, SocketMemory::$clients)) {
                //     socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                // }
            }
        }


        SocketMemory::$send = [];
    }

    private function onError($on, Throwable $error)
    {
        if (method_exists($this->class, 'onError')) {

            $this->class->onError($on, new SocketClient, $error);
            // self::send();
        }
    }
}
