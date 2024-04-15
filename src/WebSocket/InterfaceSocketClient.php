<?php

namespace Haley\WebSocket;

use Haley\WebSocket\SocketClient;
use Throwable;

interface InterfaceSocketClient
{
    public function onOpen(SocketClient $client);
    public function onMessage(string $message, int $message_id, SocketClient $client);
    public function onClose(SocketClient $client);
    public function onError(string $on, SocketClient $client, Throwable $error);
}
