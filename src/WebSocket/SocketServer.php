<?php

namespace Haley\WebSocket;

use App\Controllers\Socket\TesteController;
use Error;
use Exception;
use Haley\Router\Websocket;
use ResourceBundle;
use Socket;

class SocketServer
{
    private static ResourceBundle|Socket|false $socket  = false;
    private static array $new_clients = [];

    public static function run(array $definitions)
    {
        $class = executeCallable($definitions['action']);


        self::$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option(self::$socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind(self::$socket, $definitions['host'], $definitions['port']);
        socket_listen(self::$socket);

        SocketMemory::$clients = array(self::$socket);

        while (true) {
            usleep($definitions['usleep']);

            self::$new_clients = SocketMemory::$clients;

            socket_select(self::$new_clients, $null, $null, 0, 0);

            // if($teste)  var_dump('teste',$teste);

            foreach (self::$new_clients as $new_id => $new_client) {
                if ($new_client == self::$socket) {
                    self::connect($definitions, $class);
                    // $client = socket_accept(self::$socket);
                    // SocketMemory::$clients[] = $client;
                    // $header = socket_read($client, 30000000);
                    // self::doHandshake($header, $client, $definitions['host'], $definitions['port']);
                    // SocketMemory::$id = array_key_last(SocketMemory::$clients);       



                    // // on open
                    // SocketMemory::reset();
                    // $class->onOpen(new SocketController);

                    // foreach (SocketMemory::$clients as $id => $client_send) {

                    //     try {
                    //         if (!empty(SocketMemory::$send)) foreach (SocketMemory::$send as $msend) {
                    //             if (array_key_exists($id, $msend['id'])) {
                    //                 @socket_write($client_send, $msend['data'], strlen($msend['data']));
                    //             }
                    //         }
                    //     } catch (Exception $e) {
                    //         $error = socket_last_error($client_send);
                    //         // var_dump('onpen_close ' . $id);

                    //         var_dump('force close on open ' . $error, $e->getMessage());

                    //         if ($error === 107 || $error === 104) {
                    //             // 107: Transport endpoint is not connected
                    //             // 104: Connection reset by peer
                    //             $id = array_search($client_send, SocketMemory::$clients);
                    //             unset(SocketMemory::$clients[$id]);
                    //             socket_close($client);
                    //         }
                    //     }
                    // }

                    // $newClientIndex = array_search(self::$socket, self::$new_clients);
                    // unset(self::$new_clients[$newClientIndex]);
                } else { 
                    try {
                        $bytesReceived = socket_recv($new_client, $message, 30000000, 0);
                    } catch (Exception $e) {
                        $bytesReceived = 0;
                    }

                    // on close                   
                    if ($bytesReceived === 0 || $bytesReceived === false) {
                        $id = array_search($new_client, SocketMemory::$clients);

                        if ($id && $id !== 0) {
                            var_dump('disconected: ' . $id);
                            socket_close($new_client);
                            unset(SocketMemory::$clients[$id]);
                        }
                    }

                    // on message
                    else {
                        $message = json_decode(self::unseal($message), true);
                        var_dump('clients: ' . count(SocketMemory::$clients));
                        foreach (SocketMemory::$clients as $id => $client_send) {
                            // var_dump('clients: ' . count(SocketMemory::$clients));
                            try {
                                SocketMemory::reset();
                                SocketMemory::$id = $id;
                                $class->onMessage($message, new SocketController);

                                if (!empty(SocketMemory::$send)) foreach (SocketMemory::$send as $msend) {
                                    if (in_array($id, $msend['id'])) {
                                        @socket_write($client_send, $msend['data'], strlen($msend['data']));
                                    }
                                }

                                // @socket_write($client_send, $chat_box_message, strlen($chat_box_message));
                            } catch (Exception $e) {
                                // var_dump('fail send: ' . $e->getMessage());

                                // socket_close(SocketMemory::$clients[$id]);
                                // unset(SocketMemory::$clients[$id]);



                                // $error = socket_last_error($client_send);


                                // if ($error === 107 || $error === 104) {
                                //     var_dump('force close on message ' . $error, $e->getMessage());
                                //     // 107: Transport endpoint is not connected
                                //     // 104: Connection reset by peer
                                //     $id = array_search($client_send, SocketMemory::$clients);
                                //     unset(SocketMemory::$clients[$id]);
                                //     socket_close($client_send);
                                // }
                            }
                        }
                    }
                }
            }

            // foreach (self::$new_clients as $new_id => $new_client) {
            //     try {
            //         $bytesReceived = socket_recv($new_client, $message, 30000000, 0);

            //         // on close
            //         if ($bytesReceived === 0 || $bytesReceived === false) {

            //             $id = array_search($new_client, SocketMemory::$clients);

            //             if ($id) {

            //                 var_dump('disconected: ' . $id);
            //                 // socket_close($new_client);
            //                 unset(SocketMemory::$clients[$id]);
            //             }    

            //         }
            //     }catch(Exception $e) {

            //     }
            // }




        }

        socket_close(self::$socket);
    }

    private static function connect($definitions, $class)
    {
        $client = socket_accept(self::$socket);
        SocketMemory::$id = intval(sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000)));
        SocketMemory::$clients[SocketMemory::$id] = $client;
        $header = socket_read($client, 30000000);
        self::doHandshake($header, $client, $definitions['host'], $definitions['port']);


        // SocketMemory::$id = array_key_last(SocketMemory::$clients);

        // var_dump($header);

        // on open
        SocketMemory::reset();
        $class->onOpen(new SocketController);

        foreach (SocketMemory::$clients as $id => $client_send) {

            try {
                if (!empty(SocketMemory::$send)) foreach (SocketMemory::$send as $msend) {
                    // var_dump('send_ids',$msend['id']);

                    if (in_array($id, $msend['id'])) {
                        // var_dump('send',$client_send);
                        @socket_write($client_send, $msend['data'], strlen($msend['data']));
                    }
                }
            } catch (Exception $e) {
                $error = socket_last_error($client_send);
                // var_dump('onpen_close ' . $id);



                if ($error === 107 || $error === 104) {
                    var_dump('force close on open ' . $error, $e->getMessage());
                    // 107: Transport endpoint is not connected
                    // 104: Connection reset by peer
                    $id = array_search($client_send, SocketMemory::$clients);
                    unset(SocketMemory::$clients[$id]);
                    socket_close($client);
                }
            }
        }
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

    private static function newConnectionACK($client_ip)
    {
        $message = 'New client ' . $client_ip . ' joined';
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
