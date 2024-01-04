<?php

namespace Haley\Collections;

use Exception;
use ResourceBundle;
use Socket;

class Websocket
{
    private static ResourceBundle|Socket|false $socket  = false;
    private static array $clients = [];

    public static function run(array $definitions)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, 0, $definitions['port']);
        socket_listen($socket);

        $clients = array($socket);

        while (true) {
            $newSocketArray = $clients;
            // $newSocketArray = array_merge([$socket], $clients);
            socket_select($newSocketArray, $null, $null, 0, 0);

            if (in_array($socket, $newSocketArray)) {

                $newSocket = socket_accept($socket);
                $clients[] = $newSocket;



                $header = socket_read($newSocket, 30000000);
                self::doHandshake($header, $newSocket, $definitions['host'], $definitions['port']);

                socket_getpeername($newSocket, $client_ip_address);
                $connectionACK = self::newConnectionACK($client_ip_address);

                // on open
                foreach ($clients as $cliente_send) {
                    try {
                        @socket_write($cliente_send, $connectionACK, strlen($connectionACK));
                    } catch (Exception $e) {
                        $error = socket_last_error($cliente_send);

                        if ($error === 107 || $error === 104) {
                            // 107: Transport endpoint is not connected
                            // 104: Connection reset by peer
                            $newSocketIndex = array_search($cliente_send, $clients);
                            unset($clients[$newSocketIndex]);
                            socket_close($cliente_send);

                            var_dump('force close on open');
                        }
                    }
                }

                $newSocketIndex = array_search($socket, $newSocketArray);
                unset($newSocketArray[$newSocketIndex]);
            }

            foreach ($newSocketArray as $newSocketArrayResource) {

                while (socket_recv($newSocketArrayResource, $socketData,30000000,  0) >= 1) {

                    $socketMessage = self::unseal($socketData);
                    $messageObj = json_decode($socketMessage);
                    var_dump($messageObj);


                    if (empty($messageObj)) break 2;

                    $chat_box_message = self::createChatBoxMessage($messageObj->chat_user, $messageObj->chat_message);


                    foreach ($clients as $cliente_send) {
                        try {
                            $chat_box_message = self::createChatBoxMessage($messageObj->chat_user, $messageObj->chat_message);

                            // on message
                            var_dump('clientes: ' . count($clients) - 1);
                            @socket_write($cliente_send, $chat_box_message, strlen($chat_box_message));
                        } catch (Exception $e) {
                            $error = socket_last_error($cliente_send);

                            if ($error === 107 || $error === 104) {
                                // 107: Transport endpoint is not connected
                                // 104: Connection reset by peer
                                $newSocketIndex = array_search($cliente_send, $clients);
                                unset($clients[$newSocketIndex]);
                                socket_close($cliente_send);
                                var_dump('force close on message');
                            }
                        }
                    }

                    break 2;
                }

                socket_getpeername($newSocketArrayResource, $client_ip_address);
                $connectionACK = self::connectionDisconnectACK($client_ip_address);
                $newSocketIndex = array_search($newSocketArrayResource, $clients);
                unset($clients[$newSocketIndex]);
                socket_close($newSocketArrayResource);
                // var_dump('close');

                foreach ($clients as $cliente_send) {
                    try {
                        // on close
                        @socket_write($cliente_send, $connectionACK, strlen($connectionACK));
                    } catch (Exception $e) {
                        $error = socket_last_error($cliente_send);

                        if ($error === 107 || $error === 104) {
                            // 107: Transport endpoint is not connected
                            // 104: Connection reset by peer
                            $newSocketIndex = array_search($cliente_send, $clients);
                            unset($clients[$newSocketIndex]);
                            socket_close($cliente_send);
                            var_dump('force close on end');
                        }
                    }
                }
            }
        }

        socket_close($socket);
    }

    private static function createChatBoxMessage($chat_user, $chat_box_message)
    {
        $message = $chat_user . ": <div class='chat-box-message'>" . $chat_box_message . "</div>";
        $messageArray = array('message' => $message, 'message_type' => 'chat-box-html');
        $chatMessage = self::seal(json_encode($messageArray));
        return $chatMessage;
    }

    private static function connectionDisconnectACK($client_ip_address)
    {
        $message = 'Client ' . $client_ip_address . ' disconnected';
        $messageArray = array('message' => $message, 'message_type' => 'chat-connection-ack');
        $ACK = self::seal(json_encode($messageArray));
        return $ACK;
    }

    private static function seal($socketData)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($socketData);

        if ($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header . $socketData;
    }

    private static function  unseal($socketData)
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
        $socketData = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $socketData .= $data[$i] ^ $masks[$i % 4];
        }
        return $socketData;
    }

    private static function newConnectionACK($client_ip_address)
    {
        $message = 'New client ' . $client_ip_address . ' joined';
        $messageArray = array('message' => $message, 'message_type' => 'chat-connection-ack');
        $ACK = self::seal(json_encode($messageArray));
        return $ACK;
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

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        @socket_write($client_socket_resource, $buffer, strlen($buffer));
    }
}
