<?php

namespace Haley\WebSocket;

use Haley\Console\Lines;

use Throwable;
use ResourceBundle;
use Socket;


class SocketServer
{
    private static ResourceBundle|Socket|false $socket = false;

    private static array $clients = [];
    private static array $messages = [];
    private static int $message_id = 0;

    public static function run(array $definitions)
    {
        die;

        $class = executeCallable($definitions['action']);

        self::$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        // SO_REUSEPORT: Informa se as portas locais podem ser reutilizadas.
        socket_set_option(self::$socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option(self::$socket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024); // 1mb

        // socket_set_option(self::$socket, SOL_TCP, TCP_DEFER_ACCEPT, 1);


        // entrada
        socket_set_option(self::$socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 10, 'usec' => 0)); // 500000 - 0.5 segundos (100000)

        // saida
        // socket_set_option(self::$socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 0, 'usec' => 10));


        // criar array messages com chegagem de messagem completa


        // ----- testes

        // public function stream_open($path, $mode, $options, &$opened_path)
        // {
        //     // get rid of phpvfscomposer:// prefix for __FILE__ & __DIR__ resolution
        //     $opened_path = substr($path, 17);
        //     $this->realpath = realpath($opened_path) ?: $opened_path;
        //     $opened_path = $this->realpath;
        //     $this->handle = fopen($this->realpath, $mode);
        //     $this->position = 0;

        //     return (bool) $this->handle;
        // }

        // public function stream_read($count)
        // {
        //     $data = fread($this->handle, $count);

        //     if ($this->position === 0) {
        //         $data = preg_replace('{^#!.*\r?\n}', '', $data);
        //     }

        //     $this->position += strlen($data);

        //     return $data;
        // }






        // -------

        socket_bind(self::$socket, $definitions['host'], $definitions['port']);
        socket_listen(self::$socket);

        SocketMemory::$clients = array(self::$socket);

        var_dump(socket_get_option(self::$socket, SOL_SOCKET, SO_RCVBUF));

        while (true) {
            self::$clients = SocketMemory::$clients;

            socket_select(self::$clients, $null, $null, 0, 0);

            foreach (self::$clients as $new_client_id => $new_client) {
                socket_clear_error();

                if ($new_client == self::$socket) {
                    // connect
                    self::connect($definitions);

                    // on open
                    self::onOpen($class);
                } else {
                    if (!array_key_exists($new_client_id, self::$messages)) self::$messages[$new_client_id] = [
                        'send' => false,
                        'bytes' => 0,
                        'data' => []
                    ];

                    $last_error_code = socket_last_error($new_client);

                    if ($last_error_code) {
                        $last_error_message = socket_strerror($last_error_code);
                        Lines::red($last_error_code . ' - ' . $last_error_message)->br()->br();
                    }

                    socket_clear_error($new_client);

                    try {
                        $connected = socket_recv($new_client, $frame, 10, MSG_PEEK);

                        $fullResult = '';

                        while (0 != socket_recv($new_client, $out, 1024 * 1024, 0)) {
                            if ($out != null) $fullResult .= $out;
                        };



                        var_dump(self::unseal($fullResult));








                        // $max = 1024;
                        // $bytes = socket_recv($new_client, $frame, $max, MSG_PEEK);
                        // // $buffer = '';

                        // Lines::yellow($bytes ?? 'null')->br()->br();

                        // while ($max === $bytes) {
                        //     $max += 1024 * 10;
                        //     $bytes = socket_recv($new_client, $frame, $max, MSG_PEEK);

                        //     // if ($bytes) $buffer = $frame;

                        //     Lines::yellow($bytes ?? 'null')->br()->br();
                        // }



                        // $bytes = socket_recv($new_client, $buffer, $bytes, 0);
                        // $buffer = socket_read($new_client, $bytes, PHP_NORMAL_READ);


                        // Lines::red($bytes ?? 'null')->br()->br();

                        // var_dump(self::unseal($buffer));













                        // if ($connected) while (($buffer = socket_read($new_client, 1024 * 1000)) !== false) {
                        //     var_dump(self::unseal( substr($buffer, 0, 1024 * 100)));

                        //     // if ($buffer === '') break;
                        //     // var_dump(strlen($buffer));
                        //     // var_dump(self::unseal($buffer));



                        //     // $last = preg_match('/[\n]$/', $buffer) === 1;

                        //     // if($last) $buffer = substr($buffer, 0, -1);;

                        //     // self::$messages[$new_client_id]['bytes'] += strlen($buffer);
                        //     // self::$messages[$new_client_id]['data'][] = self::unseal($buffer);



                        //     // // $lastChars = substr($buffer, -1); // Obtém os últimos caracteres da string

                        //     // // Verifica se os últimos caracteres são "\n" ou "\r"
                        //     // if ($last) {
                        //     //     Lines::red('close');
                        //     //     break;
                        //     // }
                        // }



                        // var_dump(self::$messages[$new_client_id]);

                        // old
                        // if ($connected) while ($bytes = socket_recv($new_client, $buffer, 1024 * 10, 0)) {
                        //     // var_dump($buffer);
                        //     if ($buffer === null) {
                        //         Lines::red('null')->br()->br();
                        //         continue;
                        //     };


                        //     //  $buffer = substr($buffer, 0, $bytes);

                        //     //  $buffer = preg_replace('{^#!.*\r?\n}', '', $buffer);


                        //     $unseal = self::unseal($buffer);

                        //     var_dump($unseal);
                        //     // $first_letter = substr($unseal, 0, 1);
                        //     // $last_letter = substr($unseal, -1);

                        //     // var_dump($unseal);

                        //     // $unseal = fread($unseal, $bytes);

                        //     // if (!mb_check_encoding($unseal, 'UTF-8')) $unseal = mb_convert_encoding($unseal, 'UTF-8');

                        //     // if (!mb_check_encoding($unseal, 'UTF-8')) continue;

                        //     // // reescrver aq
                        //     // if (!empty(self::$messages[$new_client_id])) {
                        //     //     if ($first_letter != '{') {
                        //     //         self::$messages[$new_client_id] = '';
                        //     //         continue;
                        //     //     }

                        //     //     self::$messages[$new_client_id] = $buffer;
                        //     // } else {
                        //     //     self::$messages[$new_client_id] .= $buffer;
                        //     // }

                        //     self::$messages[$new_client_id]['bytes'] += $bytes;
                        //     self::$messages[$new_client_id]['data'][] = $unseal;

                        //     var_dump($bytes . ' - ' . microtime(true));

                        //     if ($bytes < 1024 * 10) {
                        //         // if ($last_letter != '}') continue;
                        //         // else $send = true;


                        //         break;
                        //     }
                        // }

                        // if (!$send) continue;

                        // $final_check = socket_recv($new_client, $frame, 100, MSG_PEEK);

                        // if ($final_check) {
                        //     var_dump([$final_check,self::unseal($frame)]);

                        //     continue;
                        // }
                    } catch (Throwable $e) {
                        Lines::red($e->getMessage() . ' - ' . $e->getFile()  . ' ' . $e->getLine())->br();
                    }

                    // on message
                    if (self::$messages[$new_client_id]['send']) self::onMessage($class, self::$messages[$new_client_id], $new_client_id);

                    // on close
                    else if (!$connected) self::onClose($class, $new_client, $new_client_id);

                    // disconnect client
                    else  if (in_array($new_client_id, SocketMemory::$close)) self::onClose($class, $new_client, $new_client_id);

                    else usleep($definitions['usleep']);
                }
            }
        }

        socket_close(self::$socket);
    }

    private static function connect($definitions)
    {
        $client = socket_accept(self::$socket);

        SocketMemory::$client_id = sprintf('%d%d', random_int(1, 10000), random_int(1, 10000));
        SocketMemory::$clients[SocketMemory::$client_id] = $client;

        socket_getpeername($client, $ip);
        SocketMemory::$ips[SocketMemory::$client_id] = $ip;

        $header = socket_read($client, 1024);
        self::doHandshake($header, $client, $definitions['host'], $definitions['port']);
    }

    private static function onOpen($class)
    {
        if (!method_exists($class, 'onOpen')) return;

        try {
            $class->onOpen(new SocketClient);

            self::send();
        } catch (Throwable $error) {
            self::onError('open', $class, $error);
        }
    }

    private static function onMessage($class, $message, $client_id)
    {
        self::$message_id++;

        // var_dump(self::unseal(substr(implode('', self::$messages[$client_id]['data']), 0, self::$messages[$client_id]['bytes'])));

        // return;

        if (!method_exists($class, 'onMessage')) {
            if (array_key_exists($client_id, self::$messages)) unset(self::$messages[$client_id]);

            return;
        };


        // die;
        Lines::blue(count($message['data']) . ' - lines : ' . $message['bytes'] . ' bytes')->br()->br();

        $message = implode('', $message['data']);

        var_dump($message);

        // if (!mb_check_encoding($message, 'UTF-8')) {
        //     $message = mb_convert_encoding($message, 'UTF-8');
        //     echo "Erro: Os dados recebidos não estão em formato UTF-8 válido." . PHP_EOL;
        // }

        // Lines::green($message)->br()->br();

        // if (!mb_check_encoding($message, 'UTF-8')) {
        // $message = mb_convert_encoding($message, 'UTF-8');
        // echo "Erro: Os dados recebidos não estão em formato UTF-8 válido." . PHP_EOL;
        // } else {
        // echo "Dados recebidos do cliente: " . $message . PHP_EOL;
        // }

        // SocketMemory::$client_id = $client_id;

        try {
            $class->onMessage($message, self::$message_id, new SocketClient);

            self::send();
        } catch (Throwable $error) {
            self::onError('message', $class, $error);
        }

        if (array_key_exists($client_id, self::$messages)) unset(self::$messages[$client_id]);
    }

    private static function onClose($class, $client, $client_id)
    {
        socket_close($client);
        unset(SocketMemory::$clients[$client_id]);
        SocketMemory::$client_id = $client_id;

        if (method_exists($class, 'onClose')) {
            try {
                $class->onClose(new SocketClient);

                self::send();
            } catch (Throwable $error) {
                self::onError('close', $class, $error);
            }
        }

        if (array_key_exists($client_id, SocketMemory::$close)) unset(SocketMemory::$close[$client_id]);
        if (array_key_exists($client_id, SocketMemory::$props)) unset(SocketMemory::$props[$client_id]);
        if (array_key_exists($client_id, SocketMemory::$ips)) unset(SocketMemory::$ips[$client_id]);
        if (array_key_exists($client_id, SocketMemory::$headers)) unset(SocketMemory::$headers[$client_id]);
    }

    private static function onError($on, $class, Throwable $error)
    {
        if (method_exists($class, 'onError')) {

            $class->onError($on, new SocketClient, $error);
            self::send();
        }
    }

    private static function send()
    {
        try {
            if (count(SocketMemory::$send)) foreach (SocketMemory::$send as $value) {
                if (count($value['id'])) foreach ($value['id'] as $to) {
                    if (array_key_exists($to, SocketMemory::$clients)) {
                        socket_write(SocketMemory::$clients[$to], $value['data'], strlen($value['data']));
                    }
                }
            }
        } catch (Throwable $error) {
            // ---
        }

        SocketMemory::$send = [];
    }

    private static function unseal($socketData)
    {
        $length = ord($socketData[1]) & 127;

        if ($length === 126) {
            $masks = substr($socketData, 4, 4);
            $data = substr($socketData, 8);
        } elseif ($length === 127) {
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

            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) $headers[$matches[1]] = $matches[2];
        }

        SocketMemory::$headers[SocketMemory::$client_id] = $headers;

        if (empty($headers['Sec-WebSocket-Key'])) return;

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $buffer  =
            // "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "HTTP/1.1 101 Switching Protocols\r\n" .

            "Content-Type: text/plain\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        @socket_write($client_socket_resource, $buffer, strlen($buffer));
    }
}
