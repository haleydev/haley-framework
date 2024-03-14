<?php

namespace Haley\WebSocket;

use Throwable;
use ResourceBundle;
use Socket;

class SocketServer
{
    private static ResourceBundle|Socket|false $socket  = false;
    private static array $clients = [];
    private static int $message_id = 0;

    public static function run(array $definitions)
    {
        $class = executeCallable($definitions['action']);

        self::$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option(self::$socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind(self::$socket, $definitions['host'], $definitions['port']);
        socket_listen(self::$socket);

        SocketMemory::$clients = array(self::$socket);

        while (true) {
            self::$clients = SocketMemory::$clients;

            socket_select(self::$clients, $null, $null, 0, 0);

            foreach (self::$clients as $new_client_id => $new_client) {
                if ($new_client == self::$socket) {
                    // connect
                    self::connect($definitions);

                    // on open
                    self::onOpen($class);
                } else {
                    try {
                        $bytesReceived = socket_recv($new_client, $message, 30000000, 0);
                    } catch (Throwable $e) {
                        $bytesReceived = 0;
                    }

                    // on close                   
                    if ($bytesReceived === 0 || $bytesReceived === false) self::onClose($class, $new_client, $new_client_id);

                    // on message
                    else self::onMessage($class, $message, $new_client_id);

                    // disconnect client
                    if (in_array($new_client_id, SocketMemory::$close)) {
                        self::onClose($class, $new_client, $new_client_id);
                    }
                }
            }

            usleep($definitions['usleep']);
        }

        socket_close(self::$socket);
    }

    private static function connect($definitions)
    {
        $client = socket_accept(self::$socket);

        SocketMemory::$id = sprintf('%d%d', random_int(1, 10000), random_int(1, 10000));
        SocketMemory::$clients[SocketMemory::$id] = $client;

        socket_getpeername($client, $ip);
        SocketMemory::$ips[SocketMemory::$id] = $ip;

        $header = socket_read($client, 30000000);
        self::doHandshake($header, $client, $definitions['host'], $definitions['port']);
    }

    private static function onOpen($class)
    {  
        if (!method_exists($class, 'onOpen')) return;
      
        SocketMemory::$send = [];    

        try {
            $class->onOpen(new SocketController);

            if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
                if (count($value['id'])) foreach ($value['id'] as $to) {
                    if (array_key_exists($to, SocketMemory::$clients)) {
                        @socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                    }
                }
            }
        } catch (Throwable $error) {       
            self::onError('open', $class, $error);
        }
    }

    private static function onMessage($class, $message, $client_id)
    {
        self::$message_id++;

        if (!method_exists($class, 'onMessage')) return;

        $message = self::unseal($message);

        SocketMemory::$send = [];
        SocketMemory::$id = $client_id;

        try {
            $class->onMessage($message, self::$message_id, new SocketController);

            if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
                if (count($value['id'])) foreach ($value['id'] as $to) {
                    if (array_key_exists($to, SocketMemory::$clients)) {
                        @socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                    }
                }
            }
        } catch (Throwable $error) {
            self::onError('message', $class, $error);
        }
    }

    private static function onClose($class, $client, $client_id)
    {
        if (method_exists($class, 'onClose')) {

            socket_close($client);
            unset(SocketMemory::$clients[$client_id]);

            SocketMemory::$send = [];
            SocketMemory::$id = $client_id;

            $class->onClose(new SocketController);

            try {
                if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
                    if (count($value['id'])) foreach ($value['id'] as $to) {
                        if (array_key_exists($to, SocketMemory::$clients)) {
                            @socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                        }
                    }
                }
            } catch (Throwable $error) {
                self::onError('close', $class, $error);
            }
        }

        if (array_key_exists($client_id, SocketMemory::$props)) unset(SocketMemory::$props[$client_id]);
        if (array_key_exists($client_id, SocketMemory::$ips)) unset(SocketMemory::$ips[$client_id]);
        if (array_key_exists($client_id, SocketMemory::$close)) unset(SocketMemory::$close[$client_id]);
    }

    private static function onError($on, $class, Throwable $error)
    {
        if (method_exists($class, 'onError')) {
            SocketMemory::$send = [];

            $class->onError($on, new SocketController, $error);

            if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
                if (count($value['id'])) foreach ($value['id'] as $to) {
                    if (array_key_exists($to, SocketMemory::$clients)) {
                        @socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                    }
                }
            }
        }
    }

    private static function unseal($socketData)
    {
        $length = ord($socketData[1]) & 127;

        if ($length == 126) {
            $masks = substr($socketData, 4, 4);
            $data = substr($socketData, 8);
        } elseif ($length == 127) {
            $masks = substr($socketData, 10, 4);
            $data = substr($socketData, 14);
        } else {
            $masks = substr($socketData, 2, 4);
            $data = substr($socketData, 6);
        }

        $socketData = '';

        for ($i = 0; $i < strlen($data); ++$i) $socketData .= $data[$i] ^ $masks[$i % 4];       

        return $socketData;
    }

    private static function doHandshake($received_header, $client_socket_resource, $host_name, $port)
    {
        $headers = array();
        $lines = preg_split("/\r\n/", $received_header);

        foreach ($lines as $line) {
            $line = chop($line);

            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        if (empty($headers['Sec-WebSocket-Key'])) return;

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        @socket_write($client_socket_resource, $buffer, strlen($buffer));
    }
}
